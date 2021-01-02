<?php

namespace Tmdb\SymfonyBundle\DependencyInjection\CompilerPass;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\Listener\Psr6CachedRequestListener;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;

class EventDispatchingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $parameters = $container->getParameter('tmdb.options');
        $clientOptions = $parameters['options'];

        $definition = $container->getDefinition($clientOptions['event_dispatcher']['adapter']);

        if ($definition->getClass() === EventDispatcher::class) {
            $this->handleSymfonyEventDispatcherRegistration($container, $definition, $parameters);
        }
    }

    private function handleSymfonyEventDispatcherRegistration(
        ContainerBuilder $container,
        Definition $eventDispatcher,
        array $parameters
    ) {
        $options = $parameters['options'];

        $cacheEnabled = $parameters['cache']['enabled'];
        $logEnabled = $parameters['log']['enabled'];
        $cacheEnabled = false;
        $logEnabled = false;

        if  ($cacheEnabled) {
            $requestListener = $container->getDefinition(Psr6CachedRequestListener::class)
                ->replaceArgument(1, new Reference($options['event_dispatcher']['adapter']))
                ->replaceArgument(2, new Reference($options['cache']['adapter']))
                ->replaceArgument(3, new Reference($options['http']['stream_factory']))
            ;
        } else {
            $requestListener = $container->getDefinition(RequestListener::class)
                ->replaceArgument(1, new Reference($options['event_dispatcher']['adapter']))
            ;
        }

        $eventDispatcher->addMethodCall('addListener', [
            RequestEvent::class,
            new Reference($requestListener->getClass())
        ]);

        $eventDispatcher->addMethodCall('addListener', [
            BeforeRequestEvent::class,
            new Reference(ApiTokenRequestListener::class)
        ]);

        $eventDispatcher->addMethodCall('addListener', [
            BeforeRequestEvent::class,
            new Reference(ContentTypeJsonRequestListener::class)
        ]);

        $eventDispatcher->addMethodCall('addListener', [
            BeforeRequestEvent::class,
            new Reference(AcceptJsonRequestListener::class)
        ]);
    }
}