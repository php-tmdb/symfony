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
use Tmdb\SymfonyBundle\Tests\DependencyInjection\TestCase;
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
