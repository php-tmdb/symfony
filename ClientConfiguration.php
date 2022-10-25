<?php

namespace Tmdb\SymfonyBundle;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tmdb\ConfigurationInterface;
use Tmdb\Token\Api\ApiToken;

/**
 * Class ClientConfiguration
 * @package Tmdb\SymfonyBundle
 */
class ClientConfiguration extends ParameterBag implements ConfigurationInterface
{
    /**
     * ClientConfiguration constructor.
     * @param ApiToken $apiToken
     * @param EventDispatcherInterface $eventDispatcher
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param UriFactoryInterface $uriFactory
     * @param array<string, mixed> $options
     */
    public function __construct(
        ApiToken $apiToken,
        EventDispatcherInterface $eventDispatcher,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        UriFactoryInterface $uriFactory,
        array $options = []
    ) {
        $options['api_token'] = $apiToken;
        $options['event_dispatcher']['adapter'] = $eventDispatcher;
        $options['http']['client'] = $client;
        $options['http']['request_factory'] = $requestFactory;
        $options['http']['response_factory'] = $responseFactory;
        $options['http']['stream_factory'] = $streamFactory;
        $options['http']['uri_factory'] = $uriFactory;

        // Library handles it as an api_token
        unset($options['bearer_token']);

        parent::__construct($options);
    }
}
