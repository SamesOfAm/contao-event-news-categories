<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = static function (): void {
    PaletteManipulator::create()
        ->addField('allowedNewsCategories', 'title_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('news_category_filter', 'tl_module');

    PaletteManipulator::create()
        ->addField('allowedEventCategories', 'title_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('event_category_filter', 'tl_module');
};

$GLOBALS['TL_DCA']['tl_module']['fields']['allowedNewsCategories'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => static function (): array {
        $connection = \Contao\System::getContainer()->get('database_connection');
        $rows = $connection->fetchAllAssociative('SELECT id, name FROM tl_news_category ORDER BY name');
        $options = [];
        foreach ($rows as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    },
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['allowedEventCategories'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => static function (): array {
        $connection = \Contao\System::getContainer()->get('database_connection');
        $rows = $connection->fetchAllAssociative('SELECT id, name FROM tl_event_category ORDER BY name');
        $options = [];
        foreach ($rows as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    },
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
