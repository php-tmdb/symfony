<?php

namespace Tmdb\SymfonyBundle;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tmdb\ConfigurationInterface;

class ClientConfiguration extends ParameterBag implements ConfigurationInterface
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param array<string> $options
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        array $options = []
    ) {
        $options['event_dispatcher'] = $eventDispatcher;

        parent::__construct($options);
    }

    /**
     * @param Cache|null $handler
     */
    public function setCacheHandler(Cache $handler = null): void
    {
        $this->parameters['cache']['handler'] = $handler;
    }
}
