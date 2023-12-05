<?php

namespace Softspring\Component\CrudlController\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ReadActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('read');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // events
                ->scalarNode('initialize_event_name')->end()
                ->scalarNode('not_found_event_name')->end()
                ->scalarNode('view_event_name')->end()

                // entity management
                ->scalarNode('entity_attribute')->defaultValue('entity')->end()
                ->scalarNode('param_converter_key')->isRequired()->end()

                // access
                ->scalarNode('is_granted')->end()

                // templates
                ->scalarNode('view')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
