<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection\CompilerPass;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;
use Tmdb\SymfonyBundle\Tests\DependencyInjection\TestCase;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;
use Tmdb\Token\Api\ApiToken;
use Tmdb\Token\Api\BearerToken;

final class ConfigurationPassTest extends TestCase
{
    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessFullConfiguration()
    {
        $container = $this->createFullConfiguration();
        $this->registerBasicServices($container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $this->doBasicAssertionsBasedOnFullOrMinimalConfig($container);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessMinimalConfiguration()
    {
        $container = $this->createMinimalConfiguration();
        $this->registerBasicServices($container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $this->doBasicAssertionsBasedOnFullOrMinimalConfig($container);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testAutowiring()
    {
        $container = new ContainerBuilder();

        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $container->register(get_class($eventDispatcherMock))->addTag(TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $container->register(get_class($httpClientMock))->addTag(TmdbSymfonyBundle::PSR18_CLIENTS);

        $requestFactoryMock = $this->createMock(RequestFactoryInterface::class);
        $container->register(get_class($requestFactoryMock))->addTag(TmdbSymfonyBundle::PSR17_REQUEST_FACTORIES);

        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $container->register(get_class($responseFactoryMock))->addTag(TmdbSymfonyBundle::PSR17_RESPONSE_FACTORIES);

        $streamFactoryMock = $this->createMock(StreamFactoryInterface::class);
        $container->register(get_class($streamFactoryMock))->addTag(TmdbSymfonyBundle::PSR17_STREAM_FACTORIES);

        $uriFactoryMock = $this->createMock(UriFactoryInterface::class);
        $container->register(get_class($uriFactoryMock))->addTag(TmdbSymfonyBundle::PSR17_URI_FACTORIES);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $this->assertTag($container, get_class($eventDispatcherMock), TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS);
        $this->assertTag($container, get_class($httpClientMock), TmdbSymfonyBundle::PSR18_CLIENTS);
        $this->assertTag($container, get_class($requestFactoryMock), TmdbSymfonyBundle::PSR17_REQUEST_FACTORIES);
        $this->assertTag($container, get_class($responseFactoryMock), TmdbSymfonyBundle::PSR17_RESPONSE_FACTORIES);
        $this->assertTag($container, get_class($streamFactoryMock), TmdbSymfonyBundle::PSR17_STREAM_FACTORIES);
        $this->assertTag($container, get_class($uriFactoryMock), TmdbSymfonyBundle::PSR17_URI_FACTORIES);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testAutowiringFailsWithUndiscoveredServices()
    {
        $this->expectException(\RuntimeException::class);
        $container = new ContainerBuilder();

        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testAutowiringFailsWithSeveralDiscoveredServices()
    {
        $this->expectException(\RuntimeException::class);

        $container = new ContainerBuilder();

        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        // mocking the same interface results in the same object? Doesn't matter for test though.
        $eventDispatcherMockTwo = $this->createMock(ClientInterface::class);

        $container->register(get_class($eventDispatcherMock))->addTag(TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS);
        $container->register(get_class($eventDispatcherMockTwo))->addTag(TmdbSymfonyBundle::PSR14_EVENT_DISPATCHERS);

        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $container);

        $pass = new ConfigurationPass();
        $pass->process($container);
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testProcessBearerToken()
    {
        $config = $this->getFullConfig();
        $config['options']['bearer_token'] = 'bearer_token';

        $container = $this->createManualConfiguration($config);
        $this->registerBasicServices($container);

        $pass = new ConfigurationPass();
        $pass->process($container);

        $this->assertEquals(
            BearerToken::class,
            $container->getDefinition('Tmdb\SymfonyBundle\ClientConfiguration')->getArgument(0)->__toString()
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerBasicServices(ContainerBuilder $container)
    {
        $container->register(EventDispatcherInterface::class);
        $container->register(ClientInterface::class);
        $container->register(RequestFactoryInterface::class);
        $container->register(ResponseFactoryInterface::class);
        $container->register(StreamFactoryInterface::class);
        $container->register(UriFactoryInterface::class);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function doBasicAssertionsBasedOnFullOrMinimalConfig(ContainerBuilder $container)
    {
        $this->assertClientConfigurationEquals($container, ApiToken::class, 0);
        $this->assertClientConfigurationEquals($container, EventDispatcherInterface::class, 1);
        $this->assertClientConfigurationEquals($container, ClientInterface::class, 2);
        $this->assertClientConfigurationEquals($container, RequestFactoryInterface::class, 3);
        $this->assertClientConfigurationEquals($container, ResponseFactoryInterface::class, 4);
        $this->assertClientConfigurationEquals($container, StreamFactoryInterface::class, 5);
        $this->assertClientConfigurationEquals($container, UriFactoryInterface::class, 6);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $expectedServiceId
     * @param int $argument
     */
    protected function assertClientConfigurationEquals(
        ContainerBuilder $container,
        string $expectedServiceId,
        int $argument
    ) {
        $this->assertEquals(
            $expectedServiceId,
            $container->getDefinition('Tmdb\SymfonyBundle\ClientConfiguration')->getArgument($argument)->__toString()
        );
    }
}
