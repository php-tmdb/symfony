Description
----------------

A Symfony2 Bundle for use together with the [wtfzdotnet/php-tmdb-api](https://github.com/wtfzdotnet/php-tmdb-api) TMDB Wrapper.

Configuration
----------------
Add to your `app/config/config.yml` the following:

```yaml
wtfz_tmdb:
    api_key: YOUR_API_KEY_HERE
```

That's all! Fire away!

__Want to make use of default caching?__

This caching system will adhere to the TMDB API max-age values, if you have different needs like long TTL's
you'd have to make your own implementation. We would be happy to intergrate more options, so please contribute.

```yaml
wtfz_tmdb:
    api_key: YOUR_API_KEY_HERE
    cache:
        enabled: true
        path: "/tmp/php-tmdb-api"

    log:
        enabled: true
        path: "/tmp/php-tmdb-api.log"
```

Usage
----------------

Obtaining the client

```php
$client = $this->get('wtfz_tmdb.client');
```

Obtaining repositories

```php
$movie = $this->get('wtfz_tmdb.movie_repository')->load(13);
```

An overview of all the repositories can be found in the services configuration [tmdb.xml](https://github.com/wtfzdotnet/WtfzTmdbBundle/blob/master/Resources/config/tmdb.xml).

There is also a Twig helper that makes use of the `Tmdb\Helper\ImageHelper` to output urls and html.

```twig
{{ movie.backdropImage|tmdb_image_url }}

{{ movie.backdropImage|tmdb_image_html('original', null, 50)|raw }}
```

**For all all other interactions take a look at [wtfzdotnet/php-tmdb-api](https://github.com/wtfzdotnet/php-tmdb-api).**