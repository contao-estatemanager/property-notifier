<?php
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'addForm';
$GLOBALS['TL_DCA']['tl_module']['palettes']['property_notifier_create'] = '{title_legend},name,headline,type;{config_legend},pnText,addForm;{expert_legend:hide},cssID';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['addForm'] = 'form';

$GLOBALS['TL_DCA']['tl_module']['fields']['pnText'] = [
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'search'                  => true,
    'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
    'sql'                     => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['addForm'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12', 'submitOnChange' => true),
    'sql'                     => "char(1) NOT NULL default ''"
];

