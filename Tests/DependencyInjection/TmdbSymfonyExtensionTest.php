<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;

final class TmdbSymfonyExtensionTest extends TestCase
{
    /**
     * @test
     * @group DependencyInjection
     */
    public function testDefaultConfigurationWithoutApiKeyThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TmdbSymfonyExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], new ContainerBuilder());
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDefaultConfigurationWithApiKey(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        $this->assertHasDefinition('Tmdb\Client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDefaultConfigurationHasLegacyAliases(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertAlias($this->container, 'Tmdb\Repository\MovieRepository', 'tmdb.movie_repository');
        $this->assertAlias($this->container, 'Tmdb\SymfonyBundle\Twig\TmdbExtension', 'tmdb.twig.image_extension');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingRepositories(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['repositories']['enabled'] = false;
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertNotAlias('tmdb.movie_repository');
        $this->assertNotHasDefinition('Tmdb\Repository\MovieRepository');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingTwig(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['twig_extension']['enabled'] = false;
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertNotHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingLegacyAliasesRemovesLegacyAliases(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['disable_legacy_aliases'] = true;
        $loader->load([$config], $this->container);

        $this->assertNotAlias('tmdb.client');
        $this->assertNotAlias('tmdb.movie_repository');
        $this->assertNotAlias('tmdb.twig.image_extension');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testLegacyMappingMapsCorrectly(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        foreach ($loader->getLegacyAliasMapping() as $group => $mapping) {
            foreach ($mapping as $alias => $serviceIdentifier) {
                $this->assertHasDefinition($serviceIdentifier);
                $this->assertAlias($this->container, $serviceIdentifier, $alias);
            }
        }
    }
}
