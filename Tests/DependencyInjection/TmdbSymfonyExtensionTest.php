<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\DependencyInjection\Container;
use Tmdb\SymfonyBundle\Tests\TestKernel;

final class TmdbSymfonyExtensionTest extends TestCase
{
    /**
     * @test
     * @group DependencyInjection
     */
    public function all_tmdb_services_can_be_loaded()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        /** @var Container $container */
        $container = $kernel->getContainer();
        $this->assertInstanceOf('Tmdb\Client', $container->get('tmdb.client'));
        $this->assertInstanceOf('Tmdb\Repository\MovieRepository', $container->get('tmdb.movie_repository'));
    }
}
