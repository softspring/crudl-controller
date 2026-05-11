<?php

declare(strict_types=1);

namespace Softspring\Component\CrudlController\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ReadActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('read');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->beforeNormalization()
            ->always()
            ->then(function (array $data): array {
                if (($data['action'] ?? 'read') !== 'read') {
                    throw new InvalidArgumentException('Read action configuration must have action read');
                }

                return $data;
            })
            ->end()
            ->children()
            ->scalarNode('action')->defaultValue('read')->end()
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
                ->arrayNode('view_data')->useAttributeAsKey('key')->prototype('variable')->end()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
