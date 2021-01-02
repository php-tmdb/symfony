<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        if (!$container->hasDefinition($parameters['options']['http']['client'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http client.',
                    $parameters['options']['http']['client']
                )
            );
        }

        $configDefinition->replaceArgument(2, new Reference($parameters['options']['http']['client']));

        if (!$container->hasDefinition($parameters['options']['http']['request_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http request factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(3, new Reference($parameters['options']['http']['request_factory']));

        if (!$container->hasDefinition($parameters['options']['http']['response_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http response factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(4, new Reference($parameters['options']['http']['response_factory']));

        if (!$container->hasDefinition($parameters['options']['http']['stream_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http stream factory.',
                    $parameters['options']['http']['request_factory']
                )
            );
        }

        $configDefinition->replaceArgument(5, new Reference($parameters['options']['http']['stream_factory']));

        if (!$container->hasDefinition($parameters['options']['http']['uri_factory'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Referenced a non existing service "%s" as http uri factory.',
                    $parameters['options']['http']['uri_factory']
                )
            );
        }

        $configDefinition->replaceArgument(6, new Reference($parameters['options']['http']['uri_factory']));
        $configDefinition->replaceArgument(7, null);
        $configDefinition->replaceArgument(8, null);
    }
}