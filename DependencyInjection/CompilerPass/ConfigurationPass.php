<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;

class ConfigurationPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $parameters = $container->getParameter('tmdb.options');
        $configDefinition = $container->getDefinition('Tmdb\SymfonyBundle\ClientConfiguration');

        $this->setupEventDispatcher($container, $configDefinition, $parameters);
        $this->setupHttpClient($container, $configDefinition, $parameters);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $configDefinition
     * @param array $parameters
     */
    private function setupEventDispatcher(
        ContainerBuilder $container,
        Definition $configDefinition,
        array $parameters
    ) {
        if (!$container->hasDefinition($parameters['options']['event_dispatcher']['adapter'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as event dispatcher.',
                    $parameters['options']['event_dispatcher']['adapter']
                )
            );
        }

        $configDefinition->replaceArgument(1, new Reference($parameters['options']['event_dispatcher']['adapter']));
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $configDefinition
     * @param array $parameters
     */
    private function setupHttpClient(
        ContainerBuilder $container,
        Definition $configDefinition,
        array $parameters
    ) {
        if ($parameters['options']['http']['client'] === null) {
            $parameters['options']['http']['client'] = $this->getApplicableServiceId(
                $container,
                TmdbSymfonyBundle::PSR18_CLIENTS,
                'tmdb_symfony.http.client'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['client'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http client.',
                    $parameters['options']['http']['client']
                )
            );
        }

        $configDefinition->replaceArgument(2, new Reference($parameters['options']['http']['client']));

        if ($parameters['options']['http']['request_factory'] === null) {
            $parameters['options']['http']['request_factory'] = $this->getApplicableServiceId(
                $container,
                TmdbSymfonyBundle::PSR17_REQUEST_FACTORIES,
                'tmdb_symfony.http.request_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['request_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http request factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(3, new Reference($parameters['options']['http']['request_factory']));

        if ($parameters['options']['http']['response_factory'] === null) {
            $parameters['options']['http']['response_factory'] = $this->getApplicableServiceId(
                $container,
                TmdbSymfonyBundle::PSR17_RESPONSE_FACTORIES,
                'tmdb_symfony.http.response_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['response_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http response factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(4, new Reference($parameters['options']['http']['response_factory']));

        if ($parameters['options']['http']['stream_factory'] === null) {
            $parameters['options']['http']['stream_factory'] = $this->getApplicableServiceId(
                $container,
                TmdbSymfonyBundle::PSR17_STREAM_FACTORIES,
                'tmdb_symfony.http.stream_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['stream_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http stream factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(5, new Reference($parameters['options']['http']['stream_factory']));

        if ($parameters['options']['http']['uri_factory'] === null) {
            $parameters['options']['http']['uri_factory'] = $this->getApplicableServiceId(
                $container,
                TmdbSymfonyBundle::PSR17_URI_FACTORIES,
                'tmdb_symfony.http.uri_factory'
            );
        }

        if (!$container->hasDefinition($parameters['options']['http']['uri_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http uri factory.',
                    $parameters['options']['http']['uri_factory']
                )
            );
        }

        $configDefinition->replaceArgument(6, new Reference($parameters['options']['http']['uri_factory']));
    }

    /**
     * @param ContainerBuilder $container
     * @param string $tag
     * @param $configurationPath
     * @return string
     * @throws \RuntimeException
     */
    protected function getApplicableServiceId(ContainerBuilder $container, string $tag, $configurationPath): string
    {
        $services = $container->findTaggedServiceIds($tag);

        if (!empty($services)) {
            if (count($services) > 1) {
                throw new RuntimeException(
                    sprintf(
                        'Trying to automatically configure tmdb symfony bundle, however we found "%d" applicable services'
                        . ' ( %s ) for tag "%s", please set one of these explicitly in your configuration under "%s".',
                        count($services),
                        implode(', ', array_keys($services)),
                        $tag,
                        $configurationPath
                    )
                );
            }

            $serviceIds = array_keys($services);
            return array_shift($serviceIds);
        }

        throw new RuntimeException(
            sprintf(
                'Unable to find any services tagged with "%s", please set it in the configuration explicitly under "%s".',
                $tag,
                $configurationPath
            )
        );
    }
}