<?php
namespace Tmdb\SymfonyBundle\Tests\Repository;

use Tmdb\Repository\ConfigurationRepository;

class FixtureConfigurationRepository extends ConfigurationRepository {
    public function load($id, array $parameters = [], array $headers = [])
    {
        return file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
            'Resources' . DIRECTORY_SEPARATOR .
            'fixtures' . DIRECTORY_SEPARATOR .
            'configuration.json'
        );
    }
}
