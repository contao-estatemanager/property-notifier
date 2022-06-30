<?php
$GLOBALS['TL_DCA']['tl_property_notifier'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
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
            'foreignKey'              => 'tl_member.name',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
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
