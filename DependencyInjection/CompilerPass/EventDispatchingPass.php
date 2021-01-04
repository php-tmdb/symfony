<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;
use Tmdb\Event\BeforeHydrationEvent;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\HttpClientExceptionEvent;
use Tmdb\Event\Listener\Logger\LogHydrationListener;
use Tmdb\Event\Listener\Psr6CachedRequestListener;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Event\ResponseEvent;
use Tmdb\Event\TmdbExceptionEvent;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;
use Tmdb\Token\Api\BearerToken;

/**
 * Class EventDispatchingCompilerPass
 * @package Tmdb\SymfonyBundle\DependencyInjection\CompilerPass
 */
class EventDispatchingPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $parameters = $container->getParameter('tmdb.options');
        $clientOptions = $parameters['options'];

        if ($container->hasAlias($clientOptions['event_dispatcher']['adapter'])) {
            $definition = $container->getDefinition(
                $container->getAlias($clientOptions['event_dispatcher']['adapter'])
            );
        } else {
            $definition = $container->getDefinition($clientOptions['event_dispatcher']['adapter']);
        }

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

        if (null !== $bearerToken = $parameters['options']['bearer_token']) {
            $definition = $container->getDefinition(ApiTokenRequestListener::class);
            $definition->replaceArgument(0, new Reference(BearerToken::class));
        }

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

        $definition = $container->getDefinition(UserAgentRequestListener::class);
        $definition->replaceArgument(
            0,
            sprintf(
                'php-tmdb/symfony/%s php-tmdb/api/%s',
                TmdbSymfonyBundle::VERSION,
                Client::VERSION
            )
        );

        $this->registerEventListener(
            $eventDispatcher,
            BeforeRequestEvent::class,
            UserAgentRequestListener::class
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
     * @param string $event
     * @param string $listener
     * @param Definition $eventDispatcher
     * @param ContainerBuilder $container
     * @param array $parameters
     */
    private function handleLogging(
        string $event,
        string $listener,
        Definition $eventDispatcher,
        ContainerBuilder $container,
        array $parameters
    ) {
        $options = $parameters[$listener];
        $configEntry = sprintf('tmdb_symfony.log.%s', $listener);

        if (!$options['enabled']) {
            return;
        }

        if (!$options['adapter']) {
            $options['adapter'] = $parameters['adapter'];
        }

        if (!$container->hasDefinition($options['adapter']) && !$container->hasAlias($options['adapter'])) {
            throw new \RuntimeException(sprintf(
                'Unable to find a definition for the adapter to provide tmdb request logging, you gave "%s" for "%s".',
                $options['adapter'],
                sprintf('%s.%s', $configEntry, 'adapter')
            ));
        }

        if (!$container->hasDefinition($options['listener']) && !$container->hasAlias($options['listener'])) {
            throw new \RuntimeException(sprintf(
                'Unable to find a definition for the listener to provide tmdb request logging, you gave "%s" for "%s".',
                $options['listener'],
                sprintf('%s.%s', $configEntry, 'listener')
            ));
        }

        if (!$container->hasDefinition($options['formatter']) && !$container->hasAlias($options['formatter'])) {
            throw new \RuntimeException(sprintf(
                'Unable to find a definition for the formatter to provide tmdb request logging, you gave "%s" for "%s".',
                $options['formatter'],
                sprintf('%s.%s', $configEntry, 'formatter')
            ));
        }

        $adapter = $container->hasAlias($options['adapter']) ?
            $container->getAlias($options['adapter']):
            $options['adapter'];

        $listenerDefinition = $container->getDefinition($options['listener']);
        $listenerDefinition->replaceArgument(0, new Reference($adapter));
        $listenerDefinition->replaceArgument(1, new Reference($options['formatter']));

        // Cannot assume if this was replaced this parameter will be kept.
        if ($listenerDefinition->getClass() === LogHydrationListener::class) {
            $listenerDefinition->replaceArgument(
                2,
                $parameters['hydration']['with_hydration_data']
            );
        }

        $this->registerEventListener(
            $eventDispatcher,
            $event,
            $listenerDefinition->getClass()
        );
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
        $listeners = [
            BeforeRequestEvent::class => 'request_logging',
            ResponseEvent::class => 'response_logging',
            HttpClientExceptionEvent::class => 'client_exception_logging',
            TmdbExceptionEvent::class => 'api_exception_logging',
            BeforeHydrationEvent::class => 'hydration'
        ];

        foreach ($listeners as $event => $listener) {
            $this->handleLogging(
                $event,
                $listener,
                $eventDispatcher,
                $container,
                $parameters['log']
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