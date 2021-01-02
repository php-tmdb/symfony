<?php

namespace Tmdb\SymfonyBundle;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\EventDispatchingCompilerPass;

/**
 * Class TmdbSymfonyBundle
 * @package Tmdb\SymfonyBundle
 */
class TmdbSymfonyBundle extends Bundle
{
    const PSR18_CLIENTS = 'tmdb_symfony.psr18.clients';
    const PSR17_REQUEST_FACTORIES = 'tmdb_symfony.psr17.request_factories';
    const PSR17_RESPONSE_FACTORIES = 'tmdb_symfony.psr17.response_factories';
    const PSR17_STREAM_FACTORIES = 'tmdb_symfony.psr17.stream_factories';
    const PSR17_URI_FACTORIES = 'tmdb_symfony.psr17.uri_factories';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new EventDispatchingCompilerPass());

        $targets = [
            ClientInterface::class => self::PSR18_CLIENTS,
            RequestFactoryInterface::class => self::PSR17_REQUEST_FACTORIES,
            ResponseFactoryInterface::class => self::PSR17_RESPONSE_FACTORIES,
            StreamFactoryInterface::class => self::PSR17_STREAM_FACTORIES,
            UriFactoryInterface::class => self::PSR17_URI_FACTORIES
        ];

        foreach ($targets as $interface => $tag) {
            $container
                ->registerForAutoconfiguration($interface)
                ->addTag($tag)
            ;
        }
    }
}
