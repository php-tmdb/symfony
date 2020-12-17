<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;

final class TmdbSymfonyExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    protected $configuration;

    public function testDefaultConfigurationWithoutApiKeyThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TmdbSymfonyExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], new ContainerBuilder());
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getEmptyConfig(): array
    {
        return [];
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDefaultConfigurationWithApiKey(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->configuration);

        $this->assertHasDefinition('Tmdb\Client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getMinimalConfig(): array
    {
        $yaml = <<<EOF
api_key: bogus
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id): void
    {
        $this->assertTrue(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDefaultConfigurationHasLegacyAliases(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->configuration);

        $this->assertAlias('Tmdb\Client', 'tmdb.client');
        $this->assertAlias('Tmdb\Repository\MovieRepository', 'tmdb.movie_repository');
        $this->assertAlias('Tmdb\SymfonyBundle\Twig\TmdbExtension', 'tmdb.twig.image_extension');
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertAlias($value, $key): void
    {
        $this->assertSame($value, (string)$this->configuration->getAlias($key), sprintf('%s alias is correct', $key));
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingRepositories(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['repositories']['enabled'] = false;
        $loader->load([$config], $this->configuration);

        $this->assertAlias('Tmdb\Client', 'tmdb.client');
        $this->assertNotAlias('tmdb.movie_repository');
        $this->assertNotHasDefinition('Tmdb\Repository\MovieRepository');
    }

    /**
     * @param string $key
     */
    private function assertNotAlias($key): void
    {
        $this->assertFalse($this->configuration->hasAlias($key), sprintf('%s alias is expected not to be registered', $key));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id): void
    {
        $this->assertFalse(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingTwig(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['twig_extension']['enabled'] = false;
        $loader->load([$config], $this->configuration);

        $this->assertAlias('Tmdb\Client', 'tmdb.client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertNotHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    /**
     * @test
     * @group DependencyInjection
     */
    public function testDisablingLegacyAliasesRemovesLegacyAliases(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['disable_legacy_aliases'] = true;
        $loader->load([$config], $this->configuration);

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
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->configuration);

        foreach ($loader->getLegacyAliasMapping() as $group => $mapping) {
            foreach ($mapping as $alias => $serviceIdentifier) {
                $this->assertHasDefinition($serviceIdentifier);
                $this->assertAlias($serviceIdentifier, $alias);
            }
        }
    }

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    protected function createEmptyConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function createMinimalConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function createFullConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getFullConfig();
        $loader->load([$config], $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
     * @return mixed
     */
    protected function getFullConfig(): array
    {
        $yaml = <<<EOF
api_key: bogus
repositories:
    enabled: true
twig_extension:
    enabled: true
disable_legacy_aliases: false
options:
    adapter: null
    secure: true
    host: api.themoviedb.org/3/
    session_token: null
    cache:
        enabled: true
        path: '%kernel.cache_dir%/themoviedb'
        handler: null
        subscriber: null
    log:
        enabled: true
        level: DEBUG
        path: '%kernel.logs_dir%/themoviedb.log'
        handler: null
        subscriber: null
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param mixed $value
     * @param string $key
     */
    private function assertParameter($value, $key): void
    {
        $this->assertSame($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}
