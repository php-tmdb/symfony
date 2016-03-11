<?php

namespace Tmdb\SymfonyBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Tmdb\SymfonyBundle\TmdbSymfonyBundle;

final class TestKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TmdbSymfonyBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }

    public function getRootDir()
    {
        return sys_get_temp_dir() . '/php-tmdb-symfony-test';
    }

    public function getCacheDir()
    {
        return $this->getRootDir() . '/cache';
    }

    public function getLogDir()
    {
        return $this->getRootDir() . '/logs';
    }
}
