<?php

namespace Softspring\Component\CrudlController\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ListActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('list');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // events
                ->scalarNode('initialize_event_name')->end()
                ->scalarNode('filter_event_name')->end()
                ->scalarNode('view_event_name')->end()

                // entity management
                ->scalarNode('entities_attribute')->defaultValue('entities')->end()

                // access
                ->scalarNode('is_granted')->end()

                // filters
                ->scalarNode('filter_form')->end()

                // templates
                ->scalarNode('view')->isRequired()->end()
                ->scalarNode('read_route')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
