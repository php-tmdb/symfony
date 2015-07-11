<?php
namespace Tmdb\SymfonyBundle;

use Doctrine\Common\Cache\Cache;
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

    public function setCacheHandler(Cache $handler = null)
    {
        $this->parameters['cache']['handler'] = $handler;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }
}
