<?php

namespace Tmdb\SymfonyBundle\DependencyInjection;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tmdb\Client;
use Tmdb\Event\Listener\Logger\LogApiErrorListener;
use Tmdb\Event\Listener\Logger\LogHttpMessageListener;
use Tmdb\Event\Listener\Logger\LogHydrationListener;
use Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter;
use Tmdb\Formatter\Hydration\SimpleHydrationFormatter;
use Tmdb\Formatter\TmdbApiException\SimpleTmdbApiExceptionFormatter;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tmdb_symfony');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addRootChildren($rootNode);
        $this->addOptionsSection($rootNode);
        $this->addLogSection($rootNode);
        $this->addCacheSection($rootNode);

        return $treeBuilder;
    }

    private function addRootChildren(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['api_key']) && !empty($v['api_key']);
                })
                ->then(function ($v) {
                    $v['options']['api_token'] = $v['api_key'];

                    return $v;
                })
            ->end()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('session_token')->defaultValue(null)->end()
                ->arrayNode('repositories')->canBeDisabled()->end()
                ->arrayNode('twig_extension')->canBeDisabled()->end()
                ->booleanNode('disable_legacy_aliases')->defaultFalse()->end()
            ->end()
        ;
    }

    private function addOptionsSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_token')
                            ->defaultValue(null)
                            ->info('Will be set by root api_key')
                        ->end()
                        ->scalarNode('bearer_token')
                            ->defaultValue(null)
                            ->info('If set will be used instead of api token')
                        ->end()
                        ->scalarNode('secure')->defaultTrue()->end()
                        ->scalarNode('host')->defaultValue(Client::TMDB_URI)->end()
                        ->scalarNode('guest_session_token')->defaultValue(null)->end()
                        ->arrayNode('event_dispatcher')
                            ->info('Reference to a service which implements PSR-14 Event Dispatcher')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('adapter')
                                ->isRequired()->cannotBeEmpty()
                                ->defaultValue(EventDispatcherInterface::class)
                            ->end()
                            ->end()
                        ->end()
                        ->arrayNode('http')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('client')
                                    ->defaultValue(ClientInterface::class)
                                    ->info('Reference to a service which implements PSR-18 HTTP Client')
                                ->end()
                                ->scalarNode('request_factory')
                                    ->defaultValue(RequestFactoryInterface::class)
                                    ->info('Reference to a service which implements PSR-17 HTTP Factories')
                                ->end()
                                ->scalarNode('response_factory')
                                    ->defaultValue(ResponseFactoryInterface::class)
                                    ->info('Reference to a service which implements PSR-17 HTTP Factories')
                                ->end()
                                ->scalarNode('stream_factory')
                                    ->defaultValue(StreamFactoryInterface::class)
                                    ->info('Reference to a service which implements PSR-17 HTTP Factories')
                                ->end()
                                ->scalarNode('uri_factory')
                                    ->defaultValue(UriFactoryInterface::class)
                                    ->info('Reference to a service which implements PSR-17 HTTP Factories')
                                ->end()
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
            ->end()
        ;
    }

    private function addLogSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('log')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('adapter')
                            ->defaultValue(LoggerInterface::class)
                            ->info('When registering a channel in monolog as "tmdb" for example, monolog.logger.tmdb')
                        ->end()
                        ->arrayNode('request_logging')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                                ->scalarNode('listener')->defaultValue(LogHttpMessageListener::class)->end()
                                ->scalarNode('adapter')->defaultValue(null)->end()
                                ->scalarNode('formatter')->defaultValue(SimpleHttpMessageFormatter::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('response_logging')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                                ->scalarNode('listener')->defaultValue(LogHttpMessageListener::class)->end()
                                ->scalarNode('adapter')->defaultValue(null)->end()
                                ->scalarNode('formatter')->defaultValue(SimpleHttpMessageFormatter::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('api_exception_logging')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                                ->scalarNode('listener')->defaultValue(LogApiErrorListener::class)->end()
                                ->scalarNode('adapter')->defaultValue(null)->end()
                                ->scalarNode('formatter')->defaultValue(SimpleTmdbApiExceptionFormatter::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('client_exception_logging')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                                ->scalarNode('listener')->defaultValue(LogHttpMessageListener::class)->end()
                                ->scalarNode('adapter')->defaultValue(null)->end()
                                ->scalarNode('formatter')->defaultValue(SimpleHttpMessageFormatter::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('hydration')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                                ->scalarNode('listener')->defaultValue(LogHydrationListener::class)->end()
                                ->scalarNode('adapter')->defaultValue(null)->end()
                                ->scalarNode('formatter')->defaultValue(SimpleHydrationFormatter::class)->end()
                                ->booleanNode('with_hydration_data')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addCacheSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('adapter')->defaultValue('Psr\Cache\CacheItemPoolInterface')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
