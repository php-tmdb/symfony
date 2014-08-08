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
        $loader->load('services.xml');

        $container->setParameter('wtfz_tmdb.api_key', $config['api_key']);

        if ($config['cache']['enabled']) {
            $path = $container->getParameterBag()->resolveValue($config['cache']['path']);
            $container->getDefinition('wtfz_tmdb.client')->addMethodCall('setCaching', array(true, $path));
        }

        if ($config['log']['enabled']) {
            $path = $container->getParameterBag()->resolveValue($config['log']['path']);
            $container->getDefinition('wtfz_tmdb.client')->addMethodCall('setLogging', array(true, $path));
        }

        if ($config['repositories']['enabled']) {
            $loader->load('repositories.xml');
        }

        if ($config['twig_extension']['enabled']) {
            $loader->load('twig.xml');
        }
    }
}
