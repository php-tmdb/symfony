<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Event\BeforeHydrationEvent;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\HttpClientExceptionEvent;
use Tmdb\Event\Listener\Logger\LogApiErrorListener;
use Tmdb\Event\Listener\Logger\LogHttpMessageListener;
use Tmdb\Event\Listener\Logger\LogHydrationListener;
use Tmdb\Event\Listener\Psr6CachedRequestListener;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Event\ResponseEvent;
use Tmdb\Event\TmdbExceptionEvent;

/**
 * Class EventDispatchingCompilerPass
 * @package Tmdb\SymfonyBundle\DependencyInjection\CompilerPass
 */
class EventDispatchingCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $parameters = $container->getParameter('tmdb.options');
        $clientOptions = $parameters['options'];

        $definition = $container->getDefinition($clientOptions['event_dispatcher']['adapter']);

        if ($definition->getClass() === EventDispatcher::class) {
            $this->handleSymfonyEventDispatcherRegistration($container, $definition, $parameters);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $eventDispatcher
     * @param array $parameters
     */
    private function handleSymfonyEventDispatcherRegistration(
        ContainerBuilder $container,
        Definition $eventDispatcher,
        array $parameters
    ) {
        $cacheEnabled = $parameters['cache']['enabled'];
        $logEnabled = $parameters['log']['enabled'];

        $requestListener = $cacheEnabled ?
            $this->getPsr6CacheRequestListener($container, $parameters):
            $this->getRequestListener($container, $parameters);

        if ($logEnabled) {
            $this->handleLoggerListeners($container, $eventDispatcher, $parameters);
        }

        $this->registerEventListener(
            $eventDispatcher,
            RequestEvent::class,
            $requestListener->getClass()
        );

        $this->registerEventListener(
            $eventDispatcher,
            BeforeRequestEvent::class,
            ApiTokenRequestListener::class
        );

        $this->registerEventListener(
            $eventDispatcher,
            BeforeRequestEvent::class,
            ContentTypeJsonRequestListener::class
        );

        $this->registerEventListener(
            $eventDispatcher,
            BeforeRequestEvent::class,
            AcceptJsonRequestListener::class
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param array $parameters
     * @return Definition
     */
    private function getRequestListener(
        ContainerBuilder $container,
        array $parameters
    ): Definition {
        return $container->getDefinition(RequestListener::class)
            ->replaceArgument(
                1,
                new Reference($parameters['options']['event_dispatcher']['adapter'])
            );
    }

    /**
     * @param ContainerBuilder $container
     * @param array $parameters
     * @return Definition
     */
    private function getPsr6CacheRequestListener(
        ContainerBuilder $container,
        array $parameters
    ): Definition {
        return $container->getDefinition(Psr6CachedRequestListener::class)
            ->replaceArgument(1, new Reference($parameters['options']['event_dispatcher']['adapter']))
            ->replaceArgument(2, new Reference($parameters['cache']['adapter']))
            ->replaceArgument(3, new Reference($parameters['options']['http']['stream_factory']));
    }

    /**
     * Register listeners for logging.
     *
     * @param ContainerBuilder $container
     * @param Definition $eventDispatcher
     * @param array $parameters
     */
    private function handleLoggerListeners(ContainerBuilder $container, Definition $eventDispatcher, array $parameters)
    {
        $requestLoggerListenerDefinition = $container->getDefinition(LogHttpMessageListener::class);
        $hydrationLoggerListenerDefinition = $container->getDefinition(LogHydrationListener::class);
        $apiErrorListenerDefinition = $container->getDefinition(LogApiErrorListener::class);

        foreach (
            [
                $requestLoggerListenerDefinition,
                $hydrationLoggerListenerDefinition,
                $apiErrorListenerDefinition
            ] as $def
        ) {
            /** @var Definition $def */
            $def->replaceArgument(0, new Reference($parameters['log']['adapter']));
        }

        if ($parameters['log']['request_logging']) {
            $this->registerEventListener(
                $eventDispatcher,
                BeforeRequestEvent::class,
                $requestLoggerListenerDefinition->getClass()
            );
        }

        if ($parameters['log']['response_logging']) {
            $this->registerEventListener(
                $eventDispatcher,
                ResponseEvent::class,
                $requestLoggerListenerDefinition->getClass()
            );
        }

        if ($parameters['log']['client_exception_logging']) {
            $this->registerEventListener(
                $eventDispatcher,
                HttpClientExceptionEvent::class,
                $requestLoggerListenerDefinition->getClass()
            );
        }

        if ($parameters['log']['api_exception_logging']) {
            $this->registerEventListener(
                $eventDispatcher,
                TmdbExceptionEvent::class,
                $apiErrorListenerDefinition->getClass()
            );
        }

        if ($parameters['log']['hydration']['enabled']) {
            $hydrationLoggerListenerDefinition->replaceArgument(
                2,
                $parameters['log']['hydration']['with_hydration_data']
            );

            $this->registerEventListener(
                $eventDispatcher,
                BeforeHydrationEvent::class,
                $hydrationLoggerListenerDefinition->getClass()
            );
        }
    }

    /**
     * @param Definition $eventDispatcher
     * @param string $event
     * @param string $reference
     */
    private function registerEventListener(
        Definition $eventDispatcher,
        string $event,
        string $reference
    ): void {
        $eventDispatcher->addMethodCall(
            'addListener',
            [
                $event,
                new Reference($reference)
            ]
        );
    }
}