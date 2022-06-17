<?php

declare(strict_types=1);

namespace ContaoEstateManager\PropertyNotifier\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoEstateManager\PropertyNotifier\EstateManagerPropertyNotifier;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(EstateManagerPropertyNotifier::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['property-notifier']),
        ];
    }
}
