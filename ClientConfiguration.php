<?php

namespace Wtfz\TmdbBundle;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tmdb\ConfigurationInterface;

class ClientConfiguration extends ParameterBag implements ConfigurationInterface {
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param array $options
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        array $options = []
    ){
        $this->parameters = $options;

        $this->parameters['event_dispatcher'] = $eventDispatcher;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }
}