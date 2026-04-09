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
     * Filters the event list by category.
     * Note: the getAllEvents hook runs after events are fetched by date range,
     * so we filter the already-collected array. Pagination reflects the
     * unfiltered count, which is acceptable for calendar-style event lists.
     */
    #[AsHook('getAllEvents')]
    public function filterByCategory(array $events, array $calendars, int $timeStart, int $timeEnd, object $module): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $events;
        }

        $categoryClass = $request->query->get('category', '');
        if ('' === $categoryClass) {
            return $events;
        }

        $category = $this->connection->fetchAssociative(
            'SELECT id FROM tl_event_category WHERE cssClass = ?',
            [$categoryClass]
        );

        if (!$category) {
            // Category not found — return empty events
            return [];
        }

        $categoryId = (string) $category['id'];

        foreach ($events as $date => $dayEvents) {
            foreach ($dayEvents as $key => $event) {
                $eventId = $event['id'] ?? null;
                if (null === $eventId) {
                    unset($events[$date][$key]);
                    continue;
                }

                $row = $this->connection->fetchAssociative(
                    'SELECT eventCategories FROM tl_calendar_events WHERE id = ?',
                    [$eventId]
                );

                if (!$row) {
                    unset($events[$date][$key]);
                    continue;
                }

                $assigned = StringUtil::deserialize($row['eventCategories'], true);
                if (!\in_array($categoryId, array_map('strval', $assigned), true)) {
                    unset($events[$date][$key]);
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
            'SELECT name, cssClass FROM tl_event_category WHERE id IN (?)',
            [$ids],
            [ArrayParameterType::INTEGER]
        );

        $template->eventCategories = $rows;
    }
}
