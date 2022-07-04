<?php

namespace ContaoEstateManager\PropertyNotifier\Cron;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoEstateManager\PropertyNotifier\NotificationTypes;
use ContaoEstateManager\PropertyNotifier\PropertyNotifier;
use NotificationCenter\Model\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PropertyNotifierCron
{
    const LAST_RUN_KEY = 'property_notifier_cron_last_run';

    private PropertyNotifier $propertyNotifier;
    private ContaoFramework $framework;

    public function __construct(PropertyNotifier $propertyNotifier, ContaoFramework $framework)
    {
        $this->propertyNotifier = $propertyNotifier;
        $this->framework = $framework;
    }

    /**
     * @Route("/property_notifier/cron", name="property_notifier_cron")
     */
    public function run(): Response
    {
        $this->framework->initialize();
        $this->runPoorMan();

        return new Response('Cronjob was executed successfully');
    }

    public function runPoorMan()
    {
        if($collection = $this->propertyNotifier->getPropertiesToNotify())
        {
            foreach ($collection as [$objNotifier, $realEstateCollection])
            {
                $objNotificationCollection = Notification::findByType(NotificationTypes::FOUND);

                if (null !== $objNotificationCollection)
                {
                    foreach ($objNotificationCollection as $objNotification)
                    {
                        $objNotification->send($this->propertyNotifier->getSimpleToken($objNotifier, $realEstateCollection));
                    }
                }

                $objNotifier->sentOn = time();
                $objNotifier->save();
            }
        }
    }

    public static function getLastRun(): int
    {
        if(!Config::has(self::LAST_RUN_KEY))
        {
            Config::set(self::LAST_RUN_KEY, 0);
            Config::persist(self::LAST_RUN_KEY, 0);
        }

        return Config::get(self::LAST_RUN_KEY);
    }

    public static function setLastRun(int $tstamp): void
    {
        Config::persist(self::LAST_RUN_KEY, $tstamp);
    }
}
