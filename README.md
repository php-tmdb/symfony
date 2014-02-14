A Symfony2 Bundle for use together with the [wtfzdotnet/php-tmdb-api](https://github.com/wtfzdotnet/php-tmdb-api) TMDB Wrapper.
==============
[![License](https://poser.pugx.org/wtfzdotnet/wtfz-tmdb-api/license.png)](https://packagist.org/packages/wtfzdotnet/wtfz-tmdb-api)


Status
----------------

Underlying library is still progressing towards stable, however most things should already be functional.
Please review the state as described in the README.md of `wtfzdotnet/php-tmdb-api`.

Usage
-----------

Grabbing the client

```php
$client = $this->get('wtfz_tmdb.client');
```

Grabbing repositories

```php
$movie = $this->get('wtfz_tmdb.movie_repository')->load(13);
```

There is also a Twig helper that makes use of the `Tmdb\Helper\ImageHelper` to output urls and html.

```twig
{{ movie.backdropImage|tmdb_image_url }}<br />

{{ movie.backdropImage|tmdb_image_html('original', null, 50)|raw }}<br />
```