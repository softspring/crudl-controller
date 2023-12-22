<?php

namespace Softspring\Component\CrudlController\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CreateActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('create');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // events
                ->scalarNode('initialize_event_name')->defaultNull()->end()
                ->scalarNode('create_entity_event_name')->defaultNull()->end()
                ->scalarNode('form_prepare_event_name')->defaultNull()->end()
                ->scalarNode('form_init_event_name')->defaultNull()->end()
                ->scalarNode('form_valid_event_name')->defaultNull()->end()
                ->scalarNode('apply_event_name')->defaultNull()->end()
                ->scalarNode('success_event_name')->defaultNull()->end()
                ->scalarNode('failure_event_name')->defaultNull()->end()
                ->scalarNode('form_invalid_event_name')->defaultNull()->end()
                ->scalarNode('view_event_name')->defaultNull()->end()
                ->scalarNode('exception_event_name')->defaultNull()->end()

                // entity management
                ->scalarNode('entity_attribute')->defaultValue('entity')->end()

                // access
                ->scalarNode('is_granted')->defaultNull()->end()

                // templates
                ->scalarNode('view')->defaultNull()->end()

                // form
                ->variableNode('form')->defaultNull()->end()

                // success
                ->scalarNode('success_redirect_to')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
