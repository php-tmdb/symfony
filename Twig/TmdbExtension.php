<?php

namespace Tmdb\SymfonyBundle\Twig;

use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Repository\ConfigurationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TmdbExtension extends AbstractExtension
{
    /**
     * @var ImageHelper|null
     */
    private $helper;

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('tmdb_image_html', array($this, 'getHtml')),
            new TwigFilter('tmdb_image_url', array($this, 'getUrl')),
        );
    }

    /**
     * @param string $image
     * @param string $size
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function getHtml(string $image, string $size = 'original', int $width = null, int $height = null): string
    {
        return $this->getHelper()->getHtml($image, $size, $width, $height);
    }

    public function getUrl($image, $size = 'original'): string
    {
        return $this->getHelper()->getUrl($image, $size);
    }

    public function getName(): string
    {
        return 'tmdb_extension';
    }

    /**
     * @param  null  $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param  ImageHelper $helper
     * @return $this
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return ImageHelper
     */
    public function getHelper()
    {
        if ($this->helper) {
            return $this->helper;
        }

        $repository = new ConfigurationRepository($this->client);
        $config     = $repository->load();

        $this->helper = new ImageHelper($config);

        return $this->helper;
    }
}
