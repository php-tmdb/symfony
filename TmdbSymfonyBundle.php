<?php

namespace Tmdb\SymfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Tmdb\SymfonyBundle\DependencyInjection\CompilerPass\EventDispatchingCompilerPass;

class TmdbSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new EventDispatchingCompilerPass());
    }
}
