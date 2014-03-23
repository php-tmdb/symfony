<?php
/**
 * This file is part of the Wrike PHP API created by B-Found IM&S.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Wrike
 * @author Michael Roterman <michael@b-found.nl>
 * @copyright (c) 2013, B-Found Internet Marketing & Services
 * @version 0.0.1
 */

namespace Wtfz\TmdbBundle\Twig;

use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Model\Image;
use Tmdb\Repository\ConfigurationRepository;

class WtfzTmdbExtension extends \Twig_Extension
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
        return 'wtfz_tmdb_extension';
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
     * @param  \Wtfz\TmdbBundle\Twig\Tmdb\Helper\ImageHelper $helper
     * @return $this
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return \Wtfz\TmdbBundle\Twig\Tmdb\Helper\ImageHelper
     */
    public function getHelper()
    {
        return $this->helper;
    }

}
