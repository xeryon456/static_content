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
            ->end();

        return $treeBuilder;
    }
}