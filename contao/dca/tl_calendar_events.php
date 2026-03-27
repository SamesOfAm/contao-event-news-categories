<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use KlapprothKoch\ContaoEventNewsCategories\DataContainer\CategoryListener;

PaletteManipulator::create()
    ->addLegend('categories_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('eventCategories', 'categories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_calendar_events');

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['eventCategories'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => [CategoryListener::class, 'getEventCategories'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
