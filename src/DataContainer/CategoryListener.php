<?php

declare(strict_types=1);

namespace KlapprothKoch\ContaoEventNewsCategories\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;

class CategoryListener
{
    public function __construct(private ?Connection $connection = null)
    {
    }

    private function getConnection(): Connection
    {
        return $this->connection ??= System::getContainer()->get('database_connection');
    }

    #[AsCallback(table: 'tl_news_category', target: 'fields.cssClass.save')]
    public function generateNewsCssClass(string $value, DataContainer $dc): string
    {
        return $this->generateCssClass($value, (string) ($dc->activeRecord?->name ?? ''));
    }

    #[AsCallback(table: 'tl_event_category', target: 'fields.cssClass.save')]
    public function generateEventCssClass(string $value, DataContainer $dc): string
    {
        return $this->generateCssClass($value, (string) ($dc->activeRecord?->name ?? ''));
    }

    #[AsCallback(table: 'tl_news', target: 'fields.newsCategories.options')]
    public function getNewsCategories(): array
    {
        return $this->fetchCategoryOptions('tl_news_category');
    }

    #[AsCallback(table: 'tl_calendar_events', target: 'fields.eventCategories.options')]
    public function getEventCategories(): array
    {
        return $this->fetchCategoryOptions('tl_event_category');
    }

    private function generateCssClass(string $value, string $name): string
    {
        if ('' !== $value) {
            return $value;
        }

        return StringUtil::generateAlias($name);
    }

    private function fetchCategoryOptions(string $table): array
    {
        $rows = $this->getConnection()->fetchAllAssociative(
            "SELECT id, name FROM {$table} ORDER BY name"
        );

        $options = [];
        foreach ($rows as $row) {
            $options[$row['id']] = $row['name'];
        }

        return $options;
    }
}
