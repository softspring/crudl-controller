<?php

namespace Softspring\Component\CrudlController\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ListActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('list');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->beforeNormalization()
            ->always()
            ->then(function ($data): array {
                if (($data['action'] ?? 'list') !== 'list') {
                    throw new InvalidArgumentException('List action configuration must have action list');
                }

                return $data;
            })
            ->end()
            ->children()
            ->scalarNode('action')->defaultValue('list')->end()
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
                ->variableNode('filter_form')->isRequired()->end()

                // templates
                ->scalarNode('view')->defaultNull()->end()
                ->scalarNode('view_page')->defaultNull()->end()
                ->arrayNode('view_data')->useAttributeAsKey('key')->prototype('variable')->end()->end()
                ->scalarNode('read_route')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
