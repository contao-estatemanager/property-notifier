<?php

use ContaoEstateManager\PropertyNotifier\EstateManager\AddonManager;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use ContaoEstateManager\PropertyNotifier\Cron\PropertyNotifierCron;
use ContaoEstateManager\PropertyNotifier\NotificationTypes;
use Contao\Config;

// Register addon
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\PropertyNotifier\EstateManager', 'AddonManager'];

if(AddonManager::valid())
{
    // Models
    $GLOBALS['TL_MODELS']['tl_property_notifier'] = PropertyNotifierModel::class;

    // Cron
    if(!Config::get('propertyNotifierPoorMan'))
    {
        $GLOBALS['TL_CRON']['minutely'][] = [PropertyNotifierCron::class, 'runPoorMan'];
    }

    // Notification Center
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['property_notifier'] = array
    (
        NotificationTypes::SAVED => [
            'recipients'           => ['notifier_*', 'member_*'],
            'email_subject'        => ['notifier_*', 'member_*'],
            'email_text'           => ['notifier_*', 'member_*', 'real_estates'],
            'email_html'           => ['notifier_*', 'member_*', 'real_estates'],
            'email_sender_name'    => ['notifier_*', 'member_*'],
            'email_sender_address' => ['notifier_*', 'member_*', 'admin_email'],
            'email_recipient_cc'   => ['notifier_*', 'member_*', 'admin_email'],
            'email_recipient_bcc'  => ['notifier_*', 'member_*', 'admin_email'],
            'email_replyTo'        => ['notifier_*', 'member_*', 'admin_email']
        ],
        NotificationTypes::FOUND => [
            'recipients'           => ['notifier_*', 'member_*'],
            'email_subject'        => ['notifier_*', 'member_*'],
            'email_text'           => ['notifier_*', 'member_*', 'real_estates'],
            'email_html'           => ['notifier_*', 'member_*', 'real_estates'],
            'email_sender_name'    => ['notifier_*', 'member_*'],
            'email_sender_address' => ['notifier_*', 'member_*', 'admin_email'],
            'email_recipient_cc'   => ['notifier_*', 'member_*', 'admin_email'],
            'email_recipient_bcc'  => ['notifier_*', 'member_*', 'admin_email'],
            'email_replyTo'        => ['notifier_*', 'member_*', 'admin_email']
        ]
    );
}
