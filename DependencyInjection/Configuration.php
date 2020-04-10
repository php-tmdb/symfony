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
            ->children()
                ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('repositories')->canBeDisabled()->end()
                ->arrayNode('twig_extension')->canBeDisabled()->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('adapter')->defaultValue(null)->end()
                        ->scalarNode('secure')->defaultValue(true)->end()
                        ->scalarNode('host')->defaultValue(Client::TMDB_URI)->end()
                        ->scalarNode('session_token')->defaultValue(null)->end()
                        ->arrayNode('cache')
                            ->canBeDisabled()
                            ->children()
                                ->scalarNode('path')->defaultValue('%kernel.cache_dir%/themoviedb')->end()
                                ->scalarNode('handler')->defaultValue(null)->end()
                                ->scalarNode('subscriber')->defaultValue(null)->end()
                            ->end()
                        ->end()
                        ->arrayNode('log')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('level')->defaultValue('DEBUG')->end()
                                ->scalarNode('path')->defaultValue('%kernel.logs_dir%/themoviedb.log')->end()
                                ->scalarNode('handler')->defaultValue(null)->end()
                                ->scalarNode('subscriber')->defaultValue(null)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
