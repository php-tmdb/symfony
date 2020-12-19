<?php

namespace Tmdb\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tmdb\ApiToken;
use Tmdb\Client;
use Tmdb\Repository\AccountRepository;
use Tmdb\Repository\AuthenticationRepository;
use Tmdb\Repository\CertificationRepository;
use Tmdb\Repository\ChangesRepository;
use Tmdb\Repository\CollectionRepository;
use Tmdb\Repository\CompanyRepository;
use Tmdb\Repository\ConfigurationRepository;
use Tmdb\Repository\CreditsRepository;
use Tmdb\Repository\DiscoverRepository;
use Tmdb\Repository\FindRepository;
use Tmdb\Repository\GenreRepository;
use Tmdb\Repository\JobsRepository;
use Tmdb\Repository\KeywordRepository;
use Tmdb\Repository\ListRepository;
use Tmdb\Repository\MovieRepository;
use Tmdb\Repository\NetworkRepository;
use Tmdb\Repository\PeopleRepository;
use Tmdb\Repository\ReviewRepository;
use Tmdb\Repository\SearchRepository;
use Tmdb\Repository\TvEpisodeRepository;
use Tmdb\Repository\TvRepository;
use Tmdb\Repository\TvSeasonRepository;
use Tmdb\SymfonyBundle\ClientConfiguration;
use Tmdb\SymfonyBundle\Twig\TmdbExtension;

/**
 * Class TmdbSymfonyExtension
 * @package Tmdb\SymfonyBundle\DependencyInjection
 */
class TmdbSymfonyExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('tmdb.api_key', $config['api_key']);

        if (!$config['disable_legacy_aliases']) {
            $this->handleLegacyGeneralAliases($container);
        }

        if ($config['repositories']['enabled']) {
            $loader->load('repositories.xml');

            if (!$config['disable_legacy_aliases']) {
                $this->handleLegacyRepositoryAliases($container);
            }
        }

        if ($config['twig_extension']['enabled']) {
            $loader->load('twig.xml');

            if (!$config['disable_legacy_aliases']) {
                $this->handleLegacyTwigExtensionAlias($container);
            }
        }

        $options = $config['options'];

        if ($options['cache']['enabled']) {
            $options = $this->handleCache($container, $options);
        }

        if ($options['log']['enabled']) {
            $options = $this->handleLog($options);
        }

        $container->setParameter('tmdb.options', $options);
    }

    /**
     * Alias mapping for legacy constructs; public to abuse within test suite.
     *
     * @return array|string[][]
     */
    public function getLegacyAliasMapping()
    {
        return [
            'repositories' => [
                'tmdb.authentication_repository' => AuthenticationRepository::class,
                'tmdb.account_repository' => AccountRepository::class,
                'tmdb.certification_repository' => CertificationRepository::class,
                'tmdb.changes_repository' => ChangesRepository::class,
                'tmdb.collection_repository' => CollectionRepository::class,
                'tmdb.company_repository' => CompanyRepository::class,
                'tmdb.configuration_repository' => ConfigurationRepository::class,
                'tmdb.credits_repository' => CreditsRepository::class,
                'tmdb.discover_repository' => DiscoverRepository::class,
                'tmdb.find_repository' => FindRepository::class,
                'tmdb.genre_repository' => GenreRepository::class,
                'tmdb.jobs_repository' => JobsRepository::class,
                'tmdb.keyword_repository' => KeywordRepository::class,
                'tmdb.list_repository' => ListRepository::class,
                'tmdb.movie_repository' => MovieRepository::class,
                'tmdb.network_repository' => NetworkRepository::class,
                'tmdb.people_repository' => PeopleRepository::class,
                'tmdb.review_repository' => ReviewRepository::class,
                'tmdb.search_repository' => SearchRepository::class,
                'tmdb.tv_repository' => TvRepository::class,
                'tmdb.tv_episode_repository' => TvEpisodeRepository::class,
                'tmdb.tv_season_repository' => TvSeasonRepository::class,
            ],
            'general' => [
                'tmdb.client' => Client::class,
                'tmdb.api_token' => ApiToken::class,
                'tmdb.configuration' => ClientConfiguration::class
            ],
            'twig' => [
                'tmdb.twig.image_extension' => TmdbExtension::class
            ]
        ];
    }

    /**
     * Performs mapping of legacy aliases to their new service identifiers.
     *
     * @param ContainerBuilder $container
     * @param array $mapping
     *
     * @return void
     */
    protected function performAliasMapping(ContainerBuilder $container, array $mapping = []): void
    {
        foreach ($mapping as $legacyAlias => $newAlias) {
            // @todo fix alias with public/private properties
            $container->setAlias($legacyAlias, new Alias($newAlias));
        }
    }

    /**
     * Handle general lgeacy aliases.
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    protected function handleLegacyGeneralAliases(ContainerBuilder $container): void
    {
        $mapping = $this->getLegacyAliasMapping();
        $this->performAliasMapping($container, $mapping['general']);
    }

    /**
     * Map repository legacy aliases
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    protected function handleLegacyRepositoryAliases(ContainerBuilder $container): void
    {
        $mapping = $this->getLegacyAliasMapping();
        $this->performAliasMapping($container, $mapping['repositories']);
    }

    /**
     * Map twig legacy aliases
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    protected function handleLegacyTwigExtensionAlias(ContainerBuilder $container): void
    {
        $mapping = $this->getLegacyAliasMapping();
        $this->performAliasMapping($container, $mapping['twig']);
    }

    /**
     * Handle cache
     *
     * @param ContainerBuilder $container
     * @param array $options
     * @return mixed
     */
    protected function handleCache(ContainerBuilder $container, array $options)
    {
        if (null !== $handler = $options['cache']['handler']) {
            $serviceId = sprintf('doctrine_cache.providers.%s', $options['cache']['handler']);

            $container->setAlias('tmdb.cache_handler', new Alias($serviceId, false));
        }

        return $options;
    }

    /**
     * Handle log
     *
     * @param array $options
     * @return array
     */
    protected function handleLog(array $options)
    {
        if (null !== $handler = $options['log']['handler']) {
            $options['log']['handler'] = !is_string($handler) ? $handler : new $handler();
        }

        return $options;
    }
}
