<?php

declare(strict_types=1);

namespace KlapprothKoch\ContaoEventNewsCategories\DataContainer;

use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class CategoryListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function generateNewsCssClass(string $value, DataContainer $dc): string
    {
        return $this->generateCssClass($value, (string) ($dc->activeRecord?->name ?? ''));
    }

    public function generateEventCssClass(string $value, DataContainer $dc): string
    {
        return $this->generateCssClass($value, (string) ($dc->activeRecord?->name ?? ''));
    }

    public function getNewsCategories(): array
    {
        return $this->fetchCategoryOptions('tl_news_category');
    }

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
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, name FROM {$table} ORDER BY name"
        );

        $options = [];
        foreach ($rows as $row) {
            $options[$row['id']] = $row['name'];
        }

        return $options;
    }
}
