<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('categories_legend', 'protected_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('allowedEventCategories', 'categories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_calendar');

$GLOBALS['TL_DCA']['tl_calendar']['fields']['allowedEventCategories'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => [\KlapprothKoch\ContaoEventNewsCategories\DataContainer\CategoryListener::class, 'getAllEventCategories'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
