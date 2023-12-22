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
                ->scalarNode('initialize_event_name')->defaultNull()->end()
                ->scalarNode('filter_form_prepare_event_name')->defaultNull()->end()
                ->scalarNode('filter_form_init_event_name')->defaultNull()->end()
                ->scalarNode('filter_event_name')->defaultNull()->end()
                ->scalarNode('view_event_name')->defaultNull()->end()
                ->scalarNode('exception_event_name')->defaultNull()->end()

                // entity management
                ->scalarNode('entity_attribute')->defaultValue('entity')->end()
                ->scalarNode('entities_attribute')->defaultValue('entities')->end()

                // access
                ->scalarNode('is_granted')->defaultNull()->end()

                // filters
                ->variableNode('filter_form')->defaultNull()->end()

                // templates
                ->scalarNode('view')->defaultNull()->end()
                ->scalarNode('view_page')->defaultNull()->end()
                ->scalarNode('read_route')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
