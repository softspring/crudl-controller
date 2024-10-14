<?php

namespace Softspring\Component\CrudlController\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DeleteActionConfiguration implements ConfigurationInterface
{
    /** @noinspection DuplicatedCode */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('delete');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->beforeNormalization()
            ->always()
            ->then(function ($data): array {
                if (($data['action'] ?? 'delete') !== 'delete') {
                    throw new InvalidArgumentException('Delete action configuration must have action delete');
                }

                return $data;
            })
            ->end()
            ->children()
            ->scalarNode('action')->defaultValue('delete')->end()
                // events
                ->scalarNode('initialize_event_name')->defaultNull()->end()
                ->scalarNode('load_entity_event_name')->defaultNull()->end()
                ->scalarNode('not_found_event_name')->defaultNull()->end()
                ->scalarNode('found_event_name')->defaultNull()->end()
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
                ->scalarNode('param_converter_key')->defaultNull()->end()

                // access
                ->scalarNode('is_granted')->defaultNull()->end()

                // templates
                ->scalarNode('view')->defaultNull()->end()
                ->arrayNode('view_data')->useAttributeAsKey('key')->prototype('variable')->end()->end()

                // form
                ->variableNode('form')->defaultNull()->end()

                // success
                ->scalarNode('success_redirect_to')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
