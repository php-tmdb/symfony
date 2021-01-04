<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection\CompilerPass;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;
use Tmdb\Event\BeforeHydrationEvent;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\HttpClientExceptionEvent;
use Tmdb\Event\Listener\HydrationListener;
use Tmdb\Event\Listener\Psr6CachedRequestListener;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\SessionTokenRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\ResponseEvent;
use Tmdb\Event\TmdbExceptionEvent;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\EventDispatchingPass;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;
use Tmdb\SymfonyBundle\Tests\DependencyInjection\TestCase;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;
use Tmdb\Token\Api\BearerToken;

final class EventDispatchingPassTest extends TestCase
{
    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessFullConfiguration()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getFullConfig();

        $this->registerBasicServices($container);
        $this->registerListenerServices($container);
        $config['options']['event_dispatcher']['adapter'] = EventDispatcher::class;

        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $pass = new EventDispatchingPass();
        $pass->process($container);

        $container->compile();
        $this->doAssertCountListenersRegistered(
            $container,
            5,
            1,
            1,
            1,
            1
        );
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessFullConfigurationWithSingleLogItemDisabled()
    {
        $container = $this->containerWithConfig([
            'log' => [
                'request_logging' => [
                    'enabled' => false
                ]
            ]
        ]);

        $container->compile();
        $this->doAssertCountListenersRegistered(
            $container,
            4,
            1,
            1,
            1,
            1
        );
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessMinimalConfiguration()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();

        $this->registerBasicServices($container);
        $this->registerListenerServices($container);

        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $pass = new EventDispatchingPass();
        $pass->process($container);

        $container->compile();
        $this->doAssertCountListenersRegistered(
            $container,
            4
        );
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testBearerToken()
    {
        $container = $this->containerWithConfig(['options' => ['bearer_token' => 'foobar']]);

        $definition = $container->getDefinition(ApiTokenRequestListener::class);
        $this->assertEquals(BearerToken::class, $definition->getArgument(0)->__toString());
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testWithFaultyAdapter()
    {
        $this->expectException(\RuntimeException::class);

        $this->containerWithConfig(['log' => ['request_logging' => ['adapter' => 'foobar']]]);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testWithFaultyFormatter()
    {
        $this->expectException(\RuntimeException::class);

        $this->containerWithConfig(['log' => ['request_logging' => ['formatter' => 'foobar']]]);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testWithFaultyListener()
    {
        $this->expectException(\RuntimeException::class);

        $this->containerWithConfig(['log' => ['request_logging' => ['listener' => 'foobar']]]);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testWithLogItemAliases()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getFullConfig();

        $this->registerBasicServices($container);
        $this->registerListenerServices($container);
        $config['options']['event_dispatcher']['adapter'] = EventDispatcher::class;

        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $pass = new EventDispatchingPass();
        $pass->process($container);

        $container->compile();
        $this->doAssertCountListenersRegistered(
            $container,
            5,
            1,
            1,
            1,
            1
        );
    }

    /**
     * @param array $faulty
     * @return ContainerBuilder
     */
    private function containerWithConfig(array $faulty = [])
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getFullConfig();

        $this->registerBasicServices($container);
        $this->registerListenerServices($container);
        $config['options']['event_dispatcher']['adapter'] = EventDispatcher::class;

        $loader->load([array_merge($config, $faulty)], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $pass = new EventDispatchingPass();
        $pass->process($container);

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerBasicServices(ContainerBuilder $container)
    {
        $container->register(EventDispatcher::class, EventDispatcher::class)->addTag(
            TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS
        );
        $container->setAlias(EventDispatcherInterface::class, EventDispatcher::class);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $container->register(get_class($httpClientMock), get_class($httpClientMock))->addTag(
            TmdbSymfonyBundle::PSR18_CLIENTS
        );

        $requestFactoryMock = $this->createMock(RequestFactoryInterface::class);
        $container->register(get_class($requestFactoryMock), get_class($requestFactoryMock))->addTag(
            TmdbSymfonyBundle::PSR17_REQUEST_FACTORIES
        );

        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $container->register(get_class($responseFactoryMock), get_class($responseFactoryMock))->addTag(
            TmdbSymfonyBundle::PSR17_RESPONSE_FACTORIES
        );

        $streamFactoryMock = $this->createMock(StreamFactoryInterface::class);
        $container->register(get_class($streamFactoryMock), get_class($streamFactoryMock))->addTag(
            TmdbSymfonyBundle::PSR17_STREAM_FACTORIES
        );

        $uriFactoryMock = $this->createMock(UriFactoryInterface::class);
        $container->register(get_class($uriFactoryMock), get_class($uriFactoryMock))->addTag(
            TmdbSymfonyBundle::PSR17_URI_FACTORIES
        );

        $cacheItemPoolMock = $this->createMock(CacheItemPoolInterface::class);
        $container->register(get_class($cacheItemPoolMock), get_class($cacheItemPoolMock));
        $container->setAlias(CacheItemPoolInterface::class, get_class($cacheItemPoolMock));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $container->register(get_class($loggerMock), get_class($loggerMock));
        $container->setAlias(LoggerInterface::class, get_class($loggerMock));
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerListenerServices(ContainerBuilder $container)
    {
        $container->register(RequestListener::class, RequestListener::class);
        $container->register(Psr6CachedRequestListener::class, Psr6CachedRequestListener::class);
        $container->register(HydrationListener::class, HydrationListener::class);
        $container->register(AcceptJsonRequestListener::class, AcceptJsonRequestListener::class);
        $container->register(ContentTypeJsonRequestListener::class, ContentTypeJsonRequestListener::class);
        $container->register(ApiTokenRequestListener::class, ApiTokenRequestListener::class);
        $container->register(SessionTokenRequestListener::class, SessionTokenRequestListener::class);
    }

    /**
     * @param ContainerBuilder $container
     * @param int $beforeRequestEventCount
     * @param int $responseEventCount
     * @param int $httpClientExceptionEventCount
     * @param int $tmdbExceptionEventCount
     * @param int $beforeHydrationEventCount
     * @throws Exception
     */
    protected function doAssertCountListenersRegistered(
        ContainerBuilder $container,
        int $beforeRequestEventCount = 0,
        int $responseEventCount = 0,
        int $httpClientExceptionEventCount = 0,
        int $tmdbExceptionEventCount = 0,
        int $beforeHydrationEventCount = 0
    ) {
        /** @var Client $client */
        $client = $container->get(Client::class);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $client->getEventDispatcher();

        $this->assertEquals(
            $beforeRequestEventCount,
            count($eventDispatcher->getListeners(BeforeRequestEvent::class))
        );

        $this->assertEquals(
            $responseEventCount,
            count($eventDispatcher->getListeners(ResponseEvent::class))
        );

        $this->assertEquals(
            $httpClientExceptionEventCount,
            count($eventDispatcher->getListeners(HttpClientExceptionEvent::class))
        );

        $this->assertEquals(
            $tmdbExceptionEventCount,
            count($eventDispatcher->getListeners(TmdbExceptionEvent::class))
        );

        $this->assertEquals(
            $beforeHydrationEventCount,
            count($eventDispatcher->getListeners(BeforeHydrationEvent::class))
        );
    }
}
