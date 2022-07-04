<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use ContaoEstateManager\PropertyNotifier\EstateManager\AddonManager;

if(AddonManager::valid())
{
    // Add field
    $GLOBALS['TL_DCA']['tl_real_estate_config']['fields']['propertyNotifierRealEstatePage'] = [
        'exclude'                 => true,
        'inputType'               => 'pageTree',
        'foreignKey'              => 'tl_page.title',
        'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'w50'),
        'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    ];

    $GLOBALS['TL_DCA']['tl_real_estate_config']['fields']['propertyNotifierExposePage'] = [
        'exclude'                 => true,
        'inputType'               => 'pageTree',
        'foreignKey'              => 'tl_page.title',
        'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'w50 clr'),
        'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    ];

    $GLOBALS['TL_DCA']['tl_real_estate_config']['fields']['propertyNotifierDeletePage'] = [
        'exclude'                 => true,
        'inputType'               => 'pageTree',
        'foreignKey'              => 'tl_page.title',
        'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'w50 clr', 'mandatory'=>true),
        'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    ];

    $GLOBALS['TL_DCA']['tl_real_estate_config']['fields']['propertyNotifierMaxCount'] = [
        'inputType'               => 'text',
        'eval'                    => array('tl_class'=>'w50 clr', 'rgxp' => 'natural')
    ];

    $GLOBALS['TL_DCA']['tl_real_estate_config']['fields']['propertyNotifierPoorMan'] = [
        'inputType'               => 'checkbox',
        'eval'                    => array('tl_class'=>'w50 m12')
    ];

    // Extend default palette
    PaletteManipulator::create()
        ->addLegend('property_notifier_legend', 'filter_config_legend', PaletteManipulator::POSITION_APPEND)
        ->addField(['propertyNotifierRealEstatePage', 'propertyNotifierExposePage', 'propertyNotifierDeletePage', 'propertyNotifierMaxCount', 'propertyNotifierPoorMan'], 'property_notifier_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_real_estate_config')
    ;
}
