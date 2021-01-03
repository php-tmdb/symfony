<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\Tests\DependencyInjection\TestCase;

final class EventDispatchingPassTest extends TestCase
{
//    public function testProcessFullConfiguration()
//    {
//        $container = $this->createFullConfiguration();
//        $container->register('event_dispatcher');
//        $container->register('http_client');
//        $container->register('psr17_factory');
//
//        $pass = new ConfigurationPass();
//        $pass->process($container);
//
//        $this->assertEquals(
//            'event_dispatcher',
//            $container->getDefinition('Tmdb\SymfonyBundle\ClientConfiguration')->getArgument(1)->__toString()
//        );
//    }
//
//    public function testProcessMinimalConfiguration()
//    {
//        $container = $this->createFullConfiguration();
//        $container->register('event_dispatcher');
//        $container->register('http_client');
//        $container->register('psr17_factory');
//
//        $pass = new ConfigurationPass();
//        $pass->process($container);
//
//        $this->assertEquals(
//            'event_dispatcher',
//            $container->getDefinition('Tmdb\SymfonyBundle\ClientConfiguration')->getArgument(1)->__toString()
//        );
//    }
}
