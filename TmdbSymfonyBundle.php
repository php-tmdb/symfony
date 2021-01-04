<?php

namespace Tmdb\SymfonyBundle;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\EventDispatchingPass;

/**
 * Class TmdbSymfonyBundle
 * @package Tmdb\SymfonyBundle
 * @codeCoverageIgnore
 */
class TmdbSymfonyBundle extends Bundle
{
    public const VERSION = '4.0.0';
    public const PSR18_CLIENTS = 'tmdb_symfony.psr18.clients';
    public const PSR17_REQUEST_FACTORIES = 'tmdb_symfony.psr17.request_factories';
    public const PSR17_RESPONSE_FACTORIES = 'tmdb_symfony.psr17.response_factories';
    public const PSR17_STREAM_FACTORIES = 'tmdb_symfony.psr17.stream_factories';
    public const PSR17_URI_FACTORIES = 'tmdb_symfony.psr17.uri_factories';
    public const PSR14_EVENT_DISPATCHERS = 'tmdb_symfony.psr17.event_dispatchers';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new EventDispatchingPass());

        $targets = [
            ClientInterface::class => self::PSR18_CLIENTS,
            RequestFactoryInterface::class => self::PSR17_REQUEST_FACTORIES,
            ResponseFactoryInterface::class => self::PSR17_RESPONSE_FACTORIES,
            StreamFactoryInterface::class => self::PSR17_STREAM_FACTORIES,
            UriFactoryInterface::class => self::PSR17_URI_FACTORIES,
            EventDispatcherInterface::class => self::PSR14_EVENT_DISPATCHERS
        ];

        foreach ($targets as $interface => $tag) {
            $container
                ->registerForAutoconfiguration($interface)
                ->addTag($tag)
            ;
        }
    }
}
