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
                ->scalarNode('initialize_event_name')->defaultNull()->end()
                ->scalarNode('load_entity_event_name')->defaultNull()->end()
                ->scalarNode('not_found_event_name')->defaultNull()->end()
                ->scalarNode('found_event_name')->defaultNull()->end()
                ->scalarNode('view_event_name')->defaultNull()->end()
                ->scalarNode('exception_event_name')->defaultNull()->end()

                // entity management
                ->scalarNode('entity_attribute')->defaultValue('entity')->end()
                ->scalarNode('param_converter_key')->defaultNull()->end()

                // access
                ->scalarNode('is_granted')->defaultNull()->end()

                // templates
                ->scalarNode('view')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
