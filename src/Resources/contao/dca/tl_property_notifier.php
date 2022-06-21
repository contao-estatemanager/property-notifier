<?php
$GLOBALS['TL_DCA']['tl_property_notifier'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},email;'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'member' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'email' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'properties' => array
        (
            'sql'                     => "blob NULL"
        ),
        'interval' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'hash' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'sentOn' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        )
    )
);
