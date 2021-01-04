<?php

namespace Tmdb\SymfonyBundle\Twig;

use PHPUnit\Framework\TestCase;
use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Model\Configuration;
use Tmdb\Model\Image;
use Tmdb\Repository\AbstractRepository;

class TmdbExtensionTest extends TestCase
{
    /**
     * @test
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

        $helper = new ImageHelper($configuration);

        $extension = new TmdbExtension($client);
        $this->assertEquals($client, $extension->getClient());

        $extension->setHelper($helper);
        $extension->setClient($client);
        $this->assertEquals($client, $extension->getClient());

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
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" />',
            $extension->getHtml($image)
        );
        $this->assertEquals('tmdb_extension', $extension->getName());
        $this->assertEquals(2, count($extension->getFilters()));
    }

    /**
     * @test
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

        $helper = new ImageHelper($configuration);

        $repository = $this->getMockBuilder(AbstractRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getApi', 'getFactory'])
            ->getMock()
        ;

        $repository->method('load')->willReturn($configuration);

        $extension = new TmdbExtension($client, $repository);
        $this->assertEquals($client, $extension->getClient());

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
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" />',
            $extension->getHtml($image)
        );
        $this->assertEquals('tmdb_extension', $extension->getName());
        $this->assertEquals(2, count($extension->getFilters()));
    }
}
