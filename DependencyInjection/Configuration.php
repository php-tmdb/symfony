<?php

namespace Wtfz\TmdbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wtfz_tmdb');

        $rootNode
            ->children()
                ->scalarNode('api_key')->end()
                ->arrayNode('cache')
                    ->children()
                        ->scalarNode('enabled')->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
                ->arrayNode('log')
                    ->children()
                        ->scalarNode('enabled')->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
