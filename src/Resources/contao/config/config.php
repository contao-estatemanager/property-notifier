<?php

use ContaoEstateManager\PropertyNotifier\AddonManager;

// Register addon
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\PropertyNotifier', 'AddonManager'];

if(AddonManager::valid())
{
    // Add backend modules and other stuff
}
