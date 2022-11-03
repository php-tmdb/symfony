<?php

namespace Tmdb\SymfonyBundle\Twig;

use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Model\Configuration;
use Tmdb\Repository\ConfigurationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TmdbExtension extends AbstractExtension
{
    private ImageHelper $helper;

    /**
     * TmdbExtension constructor.
     */
    public function __construct(Client $client, Configuration $configuration = null)
    {
        $configuration ??= (new ConfigurationRepository($client))->load();

        $this->helper = new ImageHelper($configuration);
    }

    /**
     * @return array<int, TwigFilter>
     */
    public function getFilters(): array
    {
        return array(
            new TwigFilter('tmdb_image_html', array($this, 'getHtml')),
            new TwigFilter('tmdb_image_url', array($this, 'getUrl')),
        );
    }

    public function getHtml(string $image, string $size = 'original', int $width = null, int $height = null): string
    {
        return $this->helper->getHtml($image, $size, $width, $height);
    }

    public function getUrl(string $image, string $size = 'original'): string
    {
        return $this->helper->getUrl($image, $size);
    }

    public function getName(): string
    {
        return 'tmdb_extension';
    }
}
