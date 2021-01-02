<?php

namespace Tmdb\SymfonyBundle\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tmdb\Client;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tmdb_symfony');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('session_token')->defaultValue(null)->end()
                ->arrayNode('repositories')->canBeDisabled()->end()
                ->arrayNode('twig_extension')->canBeDisabled()->end()
                ->booleanNode('disable_legacy_aliases')->defaultFalse()->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('adapter')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->arrayNode('log')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('adapter')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_token')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secure')->defaultTrue()->end()
                        ->scalarNode('host')->defaultValue(Client::TMDB_URI)->end()
                        ->scalarNode('guest_session_token')->defaultValue(null)->end()
                        ->arrayNode('event_dispatcher')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('adapter')->isRequired()->cannotBeEmpty()->defaultValue('event_dispatcher')->end()
                            ->end()
                        ->end()
                        ->arrayNode('http')
                            ->addDefaultsIfNotSet()
                            ->isRequired()
                            ->children()
                                ->scalarNode('client')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('request_factory')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('response_factory')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('stream_factory')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('uri_factory')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('hydration')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('event_listener_handles_hydration')->defaultFalse()->end()
                                ->arrayNode('only_for_specified_models')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
