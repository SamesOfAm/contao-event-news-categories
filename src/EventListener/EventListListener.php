<?php

declare(strict_types=1);

namespace KlapprothKoch\ContaoEventNewsCategories\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

class EventListListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Filters the event list by category or parentGroup.
     * - parentGroup: used on home page to show all events from sub-categories
     * - category: used on sub-pages to show events from a specific category
     */
    #[AsHook('getAllEvents')]
    public function filterByCategory(array $events, array $calendars, int $timeStart, int $timeEnd, object $module): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $events;
        }

        $parentGroup = $request->query->get('parentGroup', '');
        $categoryClass = $request->query->get('category', '');

        $categoryIds = [];

        if ('' !== $parentGroup) {
            // Récupère TOUTES les catégories qui ont ce parentGroup
            $categories = $this->connection->fetchAllAssociative(
                'SELECT id FROM tl_event_category WHERE parentGroup = ?',
                [$parentGroup]
            );

            if (empty($categories)) {
                return [];
            }

            $categoryIds = array_column($categories, 'id');
            $categoryIds = array_map('strval', $categoryIds);
        }
        elseif ('' !== $categoryClass) {
            $category = $this->connection->fetchAssociative(
                'SELECT id FROM tl_event_category WHERE cssClass = ?',
                [$categoryClass]
            );

            if (!$category) {
                return [];
            }

            $categoryIds = [(string) $category['id']];
        }

        if (empty($categoryIds)) {
            return $events;
        }

        foreach ($events as $date => $dayEvents) {
            foreach ($dayEvents as $timestamp => $timestampEvents) {
                foreach ($timestampEvents as $key => $event) {
                    $eventId = $event['id'] ?? null;
                    if (null === $eventId) {
                        unset($events[$date][$timestamp][$key]);
                        continue;
                    }

                    $row = $this->connection->fetchAssociative(
                        'SELECT eventCategories FROM tl_calendar_events WHERE id = ?',
                        [$eventId]
                    );

                    if (!$row) {
                        unset($events[$date][$timestamp][$key]);
                        continue;
                    }

                    $assigned = StringUtil::deserialize($row['eventCategories'], true);
                    $assignedStr = array_map('strval', $assigned);

                    // Vérifie si l'événement appartient à au moins une catégorie filtrée
                    if (empty(array_intersect($categoryIds, $assignedStr))) {
                        unset($events[$date][$timestamp][$key]);
                    }
                }

                if (empty($events[$date][$timestamp])) {
                    unset($events[$date][$timestamp]);
                }
            }

            if (empty($events[$date])) {
                unset($events[$date]);
            }
        }

        return $events;
    }

    #[AsHook('parseTemplate')]
    public function addCategoriesToTemplate(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'event_')) {
            return;
        }

        $ids = StringUtil::deserialize($template->eventCategories ?? '', true);

        if (empty($ids)) {
            $template->eventCategories = [];
            return;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT name, cssClass, parentGroup FROM tl_event_category WHERE id IN (?)',
            [$ids],
            [ArrayParameterType::INTEGER]
        );

        $template->eventCategories = $rows;
    }
}