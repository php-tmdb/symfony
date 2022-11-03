<?php

namespace Tmdb\SymfonyBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Tmdb\Client;
use Tmdb\Model\Configuration;
use Tmdb\Model\Image;
use Tmdb\Repository\ConfigurationRepository;
use Tmdb\SymfonyBundle\Twig\TmdbExtension;

class TmdbExtensionTest extends TestCase
{
    /**
     * @group Twig
     */
    public function testTwigExtension()
    {
        $client = $this->createMock(Client::class);
        $responseData = json_decode(
            file_get_contents(__DIR__ . '/../../Resources/test/configuration.json'),
            true
        );

        $configuration = new Configuration();
        $configuration->setImages($responseData['images']);

        $extension = new TmdbExtension($client, $configuration);

        $image = new Image();
        $image
            ->setAspectRatio(1)
            ->setFilePath('/foo.jpg')
            ->setHeight(null)
            ->setWidth(null)
            ->setIso6391('foobar')
            ->setMedia('dunno')
            ->setVoteAverage(4.7)
            ->setVoteCount(666);

        $this->assertEquals('//image.tmdb.org/t/p/original/foo.jpg', $extension->getUrl($image));
        $this->assertEquals(
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" title="" alt=""/>',
            $extension->getHtml($image)
        );
        $this->assertEquals('tmdb_extension', $extension->getName());
        $this->assertCount(2, $extension->getFilters());
    }

    /**
     * @group Twig
     */
    public function testRepository()
    {
        $client = $this->createMock(Client::class);
        $responseData = json_decode(
            file_get_contents(__DIR__ . '/../../Resources/test/configuration.json'),
            true
        );

        $configuration = new Configuration();
        $configuration->setImages($responseData['images']);

        $repository = $this->getMockBuilder(ConfigurationRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getApi', 'getFactory'])
            ->getMock()
        ;

        $repository->method('load')->willReturn($configuration);

        $extension = new TmdbExtension($client, $repository->load());

        $image = new Image();
        $image
            ->setAspectRatio(1)
            ->setFilePath('/foo.jpg')
            ->setHeight(null)
            ->setWidth(null)
            ->setIso6391('foobar')
            ->setMedia('dunno')
            ->setVoteAverage(4.7)
            ->setVoteCount(666);

        $this->assertEquals('//image.tmdb.org/t/p/original/foo.jpg', $extension->getUrl($image));
        $this->assertEquals(
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" title="" alt=""/>',
            $extension->getHtml($image)
        );
        $this->assertEquals('tmdb_extension', $extension->getName());
        $this->assertCount(2, $extension->getFilters());
    }
}
