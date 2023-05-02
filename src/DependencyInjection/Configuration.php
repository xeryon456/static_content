<?php

namespace Spontaneit\StaticContentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('static_content');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('target_folder')
                    ->defaultValue('')
                ->end()
                ->arrayNode('excluded_routes')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('excluded_prefix_routes')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('parameters')
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}