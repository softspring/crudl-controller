<?php

declare(strict_types=1);

namespace Softspring\Component\CrudlController\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CreateActionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('create');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->beforeNormalization()
                ->always()
                ->then(function (array $data): array {
                    if (($data['action'] ?? 'create') !== 'create') {
                        throw new InvalidArgumentException('Create action configuration must have action create');
                    }

                    return $data;
                })
            ->end()
            ->children()
                ->scalarNode('action')->defaultValue('create')->end()
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
