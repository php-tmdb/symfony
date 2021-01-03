<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;

class TestCase extends BaseTestCase
{
    /** @var ContainerBuilder */
    protected $container;

    /**
     * @param string $id
     */
    protected function assertHasDefinition($id): void
    {
        $this->assertTrue(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function assertAlias($value, $key): void
    {
        $this->assertSame($value, (string)$this->container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    /**
     * @param string $key
     */
    protected function assertNotAlias($key): void
    {
        $this->assertFalse($this->container->hasAlias($key), sprintf('%s alias is expected not to be registered', $key));
    }

    /**
     * @param string $id
     */
    protected function assertNotHasDefinition($id): void
    {
        $this->assertFalse(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }

    protected function tearDown(): void
    {
        $this->container = null;
    }

    /**
     * @param mixed $value
     * @param string $key
     */
    protected function assertParameter($value, $key): void
    {
        $this->assertSame($value, $this->container->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @return ContainerBuilder
     */
    protected function createEmptyConfiguration(): ContainerBuilder
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], $this->container);

        return $this->container;
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    protected function createManualConfiguration(array $config = array()): ContainerBuilder
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $loader->load([$config], $this->container);

        return $this->container;
    }

    /**
     * @return ContainerBuilder
     */
    protected function createMinimalConfiguration(): ContainerBuilder
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        return $this->container;
    }

    /**
     * @return ContainerBuilder
     */
    protected function createFullConfiguration(): ContainerBuilder
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getFullConfig();
        $loader->load([$config], $this->container);

        return $this->container;
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
     * @return mixed
     */
    protected function getFullConfig(): array
    {
        $yaml = <<<EOF
cache:
    enabled: true
    adapter: Psr\Cache\CacheItemPoolInterface
log:
    enabled: true
    adapter: Psr\EventDispatcher\EventDispatcherInterface
    hydration:
        enabled: true
        with_hydration_data: true
        adapter: null
        listener: Tmdb\Event\Listener\Logger\LogHydrationListener
        formatter: Tmdb\Formatter\Hydration\SimpleHydrationFormatter
    request_logging:
        enabled: true
        adapter: null
        listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
        formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
    response_logging:
        enabled: true
        adapter: null
        listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
        formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
    api_exception_logging:
        enabled: true
        adapter: null
        listener: Tmdb\Event\Listener\Logger\LogApiErrorListener
        formatter: Tmdb\Formatter\TmdbApiException\SimpleTmdbApiExceptionFormatter
    client_exception_logging:
        enabled: true
        adapter: null
        listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
        formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
options:
    api_token: null
    bearer_token: null
    http:
        client: Psr\Http\Client\ClientInterface
        request_factory: Psr\Http\Message\RequestFactoryInterface
        response_factory: Psr\Http\Message\ResponseFactoryInterface
        stream_factory: Psr\Http\Message\StreamFactoryInterface
        uri_factory: Psr\Http\Message\UriFactoryInterface
    secure: true
    host: api.themoviedb.org/3
    guest_session_token: null
    event_dispatcher:
        adapter: Psr\EventDispatcher\EventDispatcherInterface
    hydration:
        event_listener_handles_hydration: false
        only_for_specified_models: {  }
session_token: null
repositories:
    enabled: true
twig_extension:
    enabled: true
disable_legacy_aliases: false
api_key: api_key
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
