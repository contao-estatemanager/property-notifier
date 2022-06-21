<?php
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'addForm';
$GLOBALS['TL_DCA']['tl_module']['palettes']['property_notifier_create'] = '{title_legend},name,headline,type;{config_legend},pnText,excludeHiddenFields,addForm;{redirect_legend},jumpTo;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['property_notifier_list'] = '{title_legend},name,headline,type;{redirect_legend},jumpTo;{template_legend:hide},customTpl,notifierItemTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['addForm'] = 'form';

$GLOBALS['TL_DCA']['tl_module']['fields']['pnText'] = [
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'search'                  => true,
    'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
    'sql'                     => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['excludeHiddenFields'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['addForm'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12', 'submitOnChange' => true),
    'sql'                     => "char(1) NOT NULL default ''"
];


$GLOBALS['TL_DCA']['tl_module']['fields']['notifierItemTpl'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback' => static function ()
    {
        return \Contao\Controller::getTemplateGroup('notifier_item_');
    },
    'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(64) COLLATE ascii_bin NOT NULL default ''"
];

