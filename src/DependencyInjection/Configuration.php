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
            ->scalarNode('folder')
                ->defaultValue('')
            ->end()

            ->end();

        return $treeBuilder;
    }
}