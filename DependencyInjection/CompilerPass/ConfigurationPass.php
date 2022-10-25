<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tmdb\SymfonyBundle\ClientConfiguration;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;
use Tmdb\Token\Api\BearerToken;

class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array<string, mixed> $parameters */
        $parameters = $container->getParameter('tmdb.options');
        $configDefinition = $container->getDefinition(ClientConfiguration::class);

        // By default, the first argument is always referenced to the ApiToken.
        if (null !== $bearerToken = $parameters['options']['bearer_token']) {
            $configDefinition->replaceArgument(0, new Reference(BearerToken::class));
        }

        $this->setupEventDispatcher($container, $configDefinition, $parameters);
        $this->setupHttpClient($container, $configDefinition, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function setupEventDispatcher(
        ContainerBuilder $container,
        Definition $configDefinition,
        array $parameters
    ): void {
        if (!$container->hasDefinition($parameters['options']['event_dispatcher']['adapter'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['event_dispatcher']['adapter'],
                TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS,
                'tmdb_symfony.options.event_dispatcher.adapter'
            );
        }

        $configDefinition->replaceArgument(1, new Reference($parameters['options']['event_dispatcher']['adapter']));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function setupHttpClient(
        ContainerBuilder $container,
        Definition $configDefinition,
        array $parameters
    ): void {
        if (!$container->hasDefinition($parameters['options']['http']['client'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['http']['client'],
                TmdbSymfonyBundle::PSR18_CLIENTS,
                'tmdb_symfony.options.http.client'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['request_factory'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['http']['request_factory'],
                TmdbSymfonyBundle::PSR17_REQUEST_FACTORIES,
                'tmdb_symfony.options.http.request_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['response_factory'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['http']['response_factory'],
                TmdbSymfonyBundle::PSR17_RESPONSE_FACTORIES,
                'tmdb_symfony.options.http.response_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['stream_factory'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['http']['stream_factory'],
                TmdbSymfonyBundle::PSR17_STREAM_FACTORIES,
                'tmdb_symfony.options.http.stream_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['uri_factory'])) {
            $this->tryToAliasAutowiredInterfacesIfPossible(
                $container,
                $parameters['options']['http']['uri_factory'],
                TmdbSymfonyBundle::PSR17_URI_FACTORIES,
                'tmdb_symfony.options.http.uri_factory'
            );
        }

        $configDefinition->replaceArgument(2, new Reference($parameters['options']['http']['client']));
        $configDefinition->replaceArgument(3, new Reference($parameters['options']['http']['request_factory']));
        $configDefinition->replaceArgument(4, new Reference($parameters['options']['http']['response_factory']));
        $configDefinition->replaceArgument(5, new Reference($parameters['options']['http']['stream_factory']));
        $configDefinition->replaceArgument(6, new Reference($parameters['options']['http']['uri_factory']));
    }

    /**
     * @throws \RuntimeException
     */
    protected function tryToAliasAutowiredInterfacesIfPossible(
        ContainerBuilder $container,
        string $alias,
        string $tag,
        string $configurationPath
    ): void {
        $services = $container->findTaggedServiceIds($tag);

        if (!empty($services)) {
            if (count($services) > 1) {
                throw new RuntimeException(
                    sprintf(
                        'Trying to automatically configure tmdb symfony bundle, however we found %d applicable services'
                        . ' ( %s ) for tag "%s", please set one of these explicitly in your configuration under "%s".',
                        count($services),
                        implode(', ', array_keys($services)),
                        $tag,
                        $configurationPath
                    )
                );
            }

            $serviceIds = array_keys($services);
            $serviceId = array_shift($serviceIds);

            $container->setAlias($alias, $serviceId);
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Unable to find any services tagged with "%s", ' .
                'please set it in the configuration explicitly under "%s".',
                $tag,
                $configurationPath
            )
        );
    }
}
