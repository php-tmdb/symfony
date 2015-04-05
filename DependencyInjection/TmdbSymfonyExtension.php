<?php

namespace Tmdb\SymfonyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TmdbSymfonyExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('tmdb.api_key', $config['api_key']);

        if ($config['repositories']['enabled']) {
            $loader->load('repositories.xml');
        }

        if ($config['twig_extension']['enabled']) {
            $loader->load('twig.xml');
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

    protected function handleCache(ContainerBuilder $container, $options)
    {
        if (null !== $handler = $options['cache']['handler']) {
            $serviceId = sprintf('doctrine_cache.providers.%s', $options['cache']['handler']);

            $container->setAlias('tmdb.cache_handler', new Alias($serviceId, false));
        }

        return $options;
    }

    protected function handleLog($options)
    {
        if (null !== $handler = $options['log']['handler']) {
            $options['log']['handler'] = !is_string($handler) ? $handler: new $handler();
        }

        return $options;
    }
}
