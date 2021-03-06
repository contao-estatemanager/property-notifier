<?php
namespace ContaoEstateManager\PropertyNotifier\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('property_notifier');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('salt')
                    ->info('Custom salted key for generating the hash within the delete links')
                ->end()
                ->arrayNode('intervals')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('key')->isRequired()->end()
                            ->scalarNode('rule')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
