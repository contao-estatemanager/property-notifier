<?php
namespace ContaoEstateManager\PropertyNotifier\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EstateManagerPropertyNotifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');

        $intervals = [
            [
                'key'  => 'HOURLY',
                'rule' => 'FREQ=HOURLY'
            ],
            [
                'key'  => 'THREE_TIMES_A_DAY',
                'rule' => 'FREQ=HOURLY;BYHOUR=8,12,17'
            ],
            [
                'key'  => 'DAILY',
                'rule' => 'FREQ=DAILY;INTERVAL=1;BYHOUR=15'
            ],
            [
                'key'  => 'NEVER',
                'rule' => 'NEVER'
            ]
        ];

        if(empty($config['salt']))
        {
            $config['salt'] = '&#78;&#79;&#84;&#73;&#70;&#73;&#69;&#82;';
        }

        if(!empty($config['intervals']))
        {
            $config['intervals'] = array_merge_recursive($intervals, $config['intervals']);
        }
        else
        {
            $config['intervals'] = $intervals;
        }

        $container->setParameter('property_notifier.salt', $config['salt']);
        $container->setParameter('property_notifier.intervals', $config['intervals']);
    }
}
