<?php

use ContaoEstateManager\PropertyNotifier\EstateManager\AddonManager;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;

// Register addon
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\PropertyNotifier\EstateManager', 'AddonManager'];

if(AddonManager::valid())
{
    // Models
    $GLOBALS['TL_MODELS']['tl_property_notifier'] = PropertyNotifierModel::class;
}
