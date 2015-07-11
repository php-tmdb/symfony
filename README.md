Description
----------------

A Symfony2 Bundle for use together with the [php-tmdb/api](https://github.com/php-tmdb/api) TMDB Wrapper.

Installation
------------
Install Composer

```
$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

Add the following to your require block in composer.json config

```
"php-tmdb/symfony": "~2.0"
```

Configuration
----------------
Register the bundle in `app/AppKernel.php`:

```php
    public function registerBundles()
    {
        $bundles = array(
        ...
        new Tmdb\SymfonyBundle\TmdbSymfonyBundle(),
        ...
    }
```

Add to your `app/config/config.yml` the following:

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
```

__Configure caching__

First create a new doctrine_cache provider with a caching provider of your preference.

```yaml
doctrine_cache:
    providers:
        tmdb_cache:
            file_system:
                directory: %kernel.root_dir%/cache/tmdb
```

Then update the tmdb configuration with the alias:

```yaml
tmdb_symfony:
    options:
        cache:
            enabled: true
            handler: tmdb_cache
```

This caching system will adhere to the TMDB API max-age values, if you have different needs like long TTL's
you'd have to make your own implementation. We would be happy to intergrate more options, so please contribute.

__Want to make use of logging?__

```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    options:
        cache:
            enabled: true
        log:
            enabled: true
            #path: "%kernel.logs_dir%/tmdb.log"
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

__Full configuration with defaults :__
```yaml
tmdb_symfony:
    api_key: YOUR_API_KEY_HERE
    repositories:
        enabled: true # Set to false to disable repositories
    twig_extension:
        enabled: true # Set to false to disable twig extensions
    options:
        adapter: null
        secure: true # Set to false to disable https
        host: "api.themoviedb.org/3/"
        session_token: null
        cache:
            enabled: true # Set to false to disable cache
            path: "%kernel.cache_dir%/themoviedb"
            handler: null
            subscriber: null
        log:
            enabled: false # Set to true to enable log
            path: "%kernel.logs_dir%/themoviedb.log"
            level: DEBUG
            handler: null
            subscriber: null
```

Usage
----------------

Obtaining the client

```php
$client = $this->get('tmdb.client');
```

Obtaining repositories

```php
$movie = $this->get('tmdb.movie_repository')->load(13);
```

An overview of all the repositories can be found in the services configuration [repositories.xml](https://github.com/php-tmdb/symfony/blob/master/Resources/config/repositories.xml).

There is also a Twig helper that makes use of the `Tmdb\Helper\ImageHelper` to output urls and html.

```twig
{{ movie.backdropImage|tmdb_image_url }}

{{ movie.backdropImage|tmdb_image_html('original', null, 50)|raw }}
```

**For all all other interactions take a look at [php-tmdb/api](https://github.com/php-tmdb/api).**
