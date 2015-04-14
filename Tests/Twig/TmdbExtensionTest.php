<?php
namespace Tmdb\SymfonyBundle\Tests\Twig;

use Tmdb\Model\Movie;
use Tmdb\SymfonyBundle\Twig\TmdbExtension;

class TmdbExtensionTest extends TestCase
{
    /**
     * @var TmdbExtension
     */
    protected $extension;

    /**
     * @var Movie
     */
    protected $movie;

    /**
     * Setup
     */
    protected function setUp() {
        parent::setUp();

        $this->extension = self::$kernel->getContainer()->get('tmdb.twig.image_extension');
        $this->movie     = self::$kernel->getContainer()->get('tmdb.movie_repository')->load(1);
    }

    public function testImageUrl()
    {
        $this->assertEquals(
            'abc',
            $this->getTwig()->render('IntegrationTestBundle::tmdb_image_url.twig', array(
                'image' => $this->movie->getPosterImage(),
            ))
        );
    }
}