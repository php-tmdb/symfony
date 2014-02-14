Description
----------------
A Symfony2 Bundle for use together with the [wtfzdotnet/php-tmdb-api](https://github.com/wtfzdotnet/php-tmdb-api) TMDB Wrapper.LICENSE

Status
----------------

Underlying library is still progressing towards stable, however most things should already be functional.
Please review the state as described in the [README.md](https://github.com/wtfzdotnet/php-tmdb-api/blob/develop/README.md) TMDB Wrapper of [wtfzdotnet/php-tmdb-api](https://github.com/wtfzdotnet/php-tmdb-api/blob/develop/README.md).

Configuration
----------------
Add to your `app/config/config.yml` the following:

```yaml
wtfz_tmdb:
    api_key: YOUR_API_KEY_HERE
```

That's all! Fire away!


Usage
----------------

Grabbing the client

```php
$client = $this->get('wtfz_tmdb.client');
```

Grabbing repositories

```php
$movie = $this->get('wtfz_tmdb.movie_repository')->load(13);
```

An overview of all the repositories can be found in the services configuration [tmdb.xml](https://github.com/wtfzdotnet/WtfzTmdbBundle/blob/master/Resources/config/tmdb.xml).

There is also a Twig helper that makes use of the `Tmdb\Helper\ImageHelper` to output urls and html.

```twig
{{ movie.backdropImage|tmdb_image_url }}

{{ movie.backdropImage|tmdb_image_html('original', null, 50)|raw }}
```