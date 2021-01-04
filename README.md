# A Symfony Bundle for use together with the [php-tmdb/api](https://github.com/php-tmdb/api) TMDB API Wrapper.

[![License](https://poser.pugx.org/php-tmdb/symfony/license.png)](https://packagist.org/packages/php-tmdb/symfony)
[![License](https://img.shields.io/github/v/tag/php-tmdb/symfony)](https://github.com/php-tmdb/symfony/releases)
[![Build Status](https://img.shields.io/github/workflow/status/php-tmdb/symfony/Continuous%20Integration?label=phpunit)](https://github.com/php-tmdb/symfony/actions?query=workflow%3A%22Continuous+Integration%22)
[![Build Status](https://img.shields.io/github/workflow/status/php-tmdb/symfony/Coding%20Standards?label=phpcs)](https://github.com/php-tmdb/symfony/actions?query=workflow%3A%22Coding+Standards%22)
[![codecov](https://img.shields.io/codecov/c/github/php-tmdb/symfony?token=gTM9AiO5vH)](https://codecov.io/gh/php-tmdb/symfony)
[![PHP](https://img.shields.io/badge/php->=7.3,%20>=8.0-8892BF.svg)](https://packagist.org/packages/php-tmdb/symfony)
[![Total Downloads](https://poser.pugx.org/php-tmdb/symfony/downloads.svg)](https://packagist.org/packages/php-tmdb/symfony)

Compatible with Symfony 4 and 5, PHP 7.3 and up.

## Buy me a coffee, or a beer :-)

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SMLZ362KQ8K8W"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"></a>

My stomach will appreciate your donation! 

Installation
------------

- [Install Composer](https://getcomposer.org/doc/00-intro.md)
- [Install php-tmdb/api dependencies](https://github.com/php-tmdb/api#installation)
    - For development within Symfony we recommend making use of Symfony's PSR-18 HTTP Client _`Symfony\Component\HttpClient\Psr18Client`_,
      as when non-cached results pass your profiler will be filled with data.

Then require the bundle:

```
composer require php-tmdb/symfony:^4
```

Configuration
----------------
Register the bundle in `app/bundles.php`:

```php
<?php

return [
    // --- snip ---
    Tmdb\SymfonyBundle\TmdbSymfonyBundle::class => ['all' => true],
];
```

Add to your `app/config/config.yml` the following, or replace values with services of your choice ( PSR-18 Http Client / PSR-17 Factories ):

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    options:
        http:
            client: Symfony\Component\HttpClient\Psr18Client
            request_factory: Nyholm\Psr7\Factory\Psr17Factory
            response_factory: Nyholm\Psr7\Factory\Psr17Factory
            stream_factory: Nyholm\Psr7\Factory\Psr17Factory
            uri_factory: Nyholm\Psr7\Factory\Psr17Factory
```

`services.yaml`:

```yaml
services:
    Symfony\Component\HttpClient\Psr18Client:
        class: Symfony\Component\HttpClient\Psr18Client

    Nyholm\Psr7\Factory\Psr17Factory:
        class: Nyholm\Psr7\Factory\Psr17Factory
```

__Configure caching__

You can use any PSR-6 cache you wish to use, we will simply use symfony's cache.

When making use of caching, make sure to also include `php-http/cache-plugin` in composer, this plugin handles the logic for us, 
so we don't have to re-invent the wheel. 

You are however also free to choose to implement your own cache listener, or add the caching logic inside the http client of your choice.

```shell script
composer require php-http/cache-plugin:^1.7
```

First off configure the cache pool in symfony `config/cache.yaml`:

```yaml
framework:
    cache:
        pools:
            cache.tmdb:
                adapter: cache.adapter.filesystem
                default_lifetime: 86400
```

Then in your `tmdb_symfony.yaml` configuration enable the cache and reference this cache pool:

```yaml
tmdb_symfony:
  api_key: YOUR_API_KEY_HERE
  cache:
    enabled: true
    adapter: cache.tmdb
```

__Want to make use of logging?__

Logging capabilities as of `4.0` allow you to make a fine-grained configuration.

You can use any PSR-3 logger you wish to use, we will simply use monolog. 

First off configure the monolog and add a channel and handler:

```yaml
monolog:
    channels:
        - tmdb
    handlers:
        tmdb:
            type: stream
            path: "%kernel.logs_dir%/php-tmdb--symfony.%kernel.environment%.log"
            level: info
            channels: ["tmdb"]
```

Then in your `tmdb_symfony.yaml` configuration:

```yaml
tmdb_symfony:
  api_key: YOUR_API_KEY_HERE
  log:
    enabled: true
    adapter: monolog.logger.tmdb
    hydration:
      enabled: true
      with_hydration_data: false # We would only recommend to enable this with an in-memory logger, so you have access to the hydration data within the profiler.
      adapter: null # you can set different adapters for different logs, leave null to use the main adapter.
      listener: Tmdb\Event\Listener\Logger\LogHydrationListener
      formatter: Tmdb\Formatter\Hydration\SimpleHydrationFormatter
    request_logging:
      enabled: true
      adapter: null # you can set different adapters for different logs, leave null to use the main adapter.
      listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
      formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
    response_logging:
      enabled: true
      adapter: null # you can set different adapters for different logs, leave null to use the main adapter.
      listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
      formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
    api_exception_logging:
      enabled: true
      adapter: null # you can set different adapters for different logs, leave null to use the main adapter.
      listener: Tmdb\Event\Listener\Logger\LogApiErrorListener
      formatter: Tmdb\Formatter\TmdbApiException\SimpleTmdbApiExceptionFormatter
    client_exception_logging:
      enabled: true
      adapter: null # you can set different adapters for different logs, leave null to use the main adapter.
      listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
      formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
```

__Disable repositories :__

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    repositories:
        enabled: false
```

__Disable twig extension :__

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    twig_extension:
        enabled: false
```
__Disable https :__

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    options:
        secure:
            enabled: false
```

__Disable legacy aliases :__

_Set to true to remove all legacy alises ( e.g. `tmdb.client` or `tmdb.movie_repository` )._

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    disable_legacy_aliases: true
```

__Full configuration with defaults :__
```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    cache:
        enabled: true
        adapter: cache.tmdb
    log:
        enabled: true
        adapter: monolog.logger.tmdb
        hydration:
            enabled: true
            with_hydration_data: false
            adapter: null
            listener: Tmdb\Event\Listener\Logger\LogHydrationListener
            formatter: Tmdb\Formatter\Hydration\SimpleHydrationFormatter
        request_logging:
            enabled: true
            adapter: null
            listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
            formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
        response_logging:
            enabled: true
            adapter: null
            listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
            formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
        api_exception_logging:
            enabled: true
            adapter: null
            listener: Tmdb\Event\Listener\Logger\LogApiErrorListener
            formatter: Tmdb\Formatter\TmdbApiException\SimpleTmdbApiExceptionFormatter
        client_exception_logging:
            enabled: true
            adapter: null
            listener: Tmdb\Event\Listener\Logger\LogHttpMessageListener
            formatter: Tmdb\Formatter\HttpMessage\SimpleHttpMessageFormatter
    options:
        bearer_token: YOUR_BEARER_TOKEN_HERE
        http:
            client: Symfony\Component\HttpClient\Psr18Client
            request_factory: Nyholm\Psr7\Factory\Psr17Factory
            response_factory: Nyholm\Psr7\Factory\Psr17Factory
            stream_factory: Nyholm\Psr7\Factory\Psr17Factory
            uri_factory: Nyholm\Psr7\Factory\Psr17Factory
        secure: true
        host: api.themoviedb.org/3
        guest_session_token: null
        event_dispatcher:
            adapter: event_dispatcher
        hydration:
            event_listener_handles_hydration: false
            only_for_specified_models: {  }
        api_token: YOUR_API_KEY_HERE # you don't have to set this if you set it at the root level
    session_token: null
    repositories:
        enabled: true
    twig_extension:
        enabled: true
    disable_legacy_aliases: false
```

Usage
----------------

Obtaining the client

```php
$client = $this->get(Tmdb\Client::class);
```

Obtaining repositories

```php
$movie = $this->get(\Tmdb\Repository\MovieRepository::class)->load(13);
```

An overview of all the repositories can be found in the services configuration [repositories.xml](https://github.com/php-tmdb/symfony/blob/master/Resources/config/repositories.xml).

There is also a Twig helper that makes use of the `Tmdb\Helper\ImageHelper` to output urls and html.

```twig
{{ movie.backdropImage|tmdb_image_url }}

{{ movie.backdropImage|tmdb_image_html('original', null, 50)|raw }}
```

**For all all other interactions take a look at [php-tmdb/api](https://github.com/php-tmdb/api).**
