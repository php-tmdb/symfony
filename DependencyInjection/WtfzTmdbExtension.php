<?php

namespace Wtfz\TmdbBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WtfzTmdbExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('tmdb.xml');

        if (!isset($config['api_key'])) {
            throw new \InvalidArgumentException(
                'The "api_key" option must be set'
            );
        }

        $container->setParameter('wtfz_tmdb.api_key', $config['api_key']);

        if (array_key_exists('cache', $config)) {
            $cacheEnabled = array_key_exists('enabled', $config['cache']) && $config['cache']['enabled'];
            $cachePath    = array_key_exists('path', $config['cache']) ? $config['cache']['path'] : null;

            $container->setParameter('wtfz_tmdb.cache.enabled', $cacheEnabled);
            $container->setParameter('wtfz_tmdb.cache.path', $cachePath);
        }

        if (array_key_exists('log', $config)) {
            $logEnabled = array_key_exists('enabled', $config['log']) && $config['log']['enabled'];
            $logPath    = array_key_exists('path', $config['log']) ? $config['log']['path'] : null;

            $container->setParameter('wtfz_tmdb.log.enabled', $logEnabled);
            $container->setParameter('wtfz_tmdb.log.path', $logPath);
        }
    }
}
