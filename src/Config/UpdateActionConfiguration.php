<?php

namespace Softspring\Component\CrudlController\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class UpdateActionConfiguration implements ConfigurationInterface
{
    /** @noinspection DuplicatedCode */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('update');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // events
                ->scalarNode('initialize_event_name')->end()
                ->scalarNode('not_found_event_name')->end()
                ->scalarNode('form_prepare_event_name')->end()
                ->scalarNode('form_init_event_name')->end()
                ->scalarNode('form_valid_event_name')->end()
                ->scalarNode('success_event_name')->end()
                ->scalarNode('exception_event_name')->end()
                ->scalarNode('form_invalid_event_name')->end()
                ->scalarNode('view_event_name')->end()

                // entity management
                ->scalarNode('entity_attribute')->defaultValue('entity')->end()
                ->scalarNode('param_converter_key')->isRequired()->end()

                // access
                ->scalarNode('is_granted')->end()

                // templates
                ->scalarNode('view')->isRequired()->end()

                // form
                ->scalarNode('form')->isRequired()->end()

                // success
                ->scalarNode('success_redirect_to')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
