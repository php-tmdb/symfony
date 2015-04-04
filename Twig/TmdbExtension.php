<?php
namespace Tmdb\SymfonyBundle\Twig;

use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Model\Image;
use Tmdb\Repository\ConfigurationRepository;

class TmdbExtension extends \Twig_Extension
{
    private $helper;

    private $client;

    private $configuration;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $repository = new ConfigurationRepository($client);
        $config     = $repository->load();

        $this->helper = new ImageHelper($config);
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('tmdb_image_html', array($this, 'getHtml')),
            new \Twig_SimpleFilter('tmdb_image_url', array($this, 'getUrl')),
        );
    }

    public function getHtml($image, $size = 'original', $width = null, $height = null)
    {
        return $this->helper->getHtml($image, $size, $width, $height);
    }

    public function getUrl($image)
    {
        return $this->helper->getUrl($image);
    }

    public function getName()
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
     * @return null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param  mixed $configuration
     * @return $this
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
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
        return $this->helper;
    }
}
