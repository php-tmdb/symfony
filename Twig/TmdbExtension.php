<?php

namespace Tmdb\SymfonyBundle\Twig;

use Tmdb\Client;
use Tmdb\Helper\ImageHelper;
use Tmdb\Repository\AbstractRepository;
use Tmdb\Repository\ConfigurationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TmdbExtension extends AbstractExtension
{
    private ?ImageHelper $helper;

    private Client $client;

    private ConfigurationRepository $repository;

    /**
     * TmdbExtension constructor.
     */
    public function __construct(Client $client, ConfigurationRepository $repository = null)
    {
        $this->client = $client;
        $this->repository = $repository ?? new ConfigurationRepository($client);
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
        return $this->getHelper()->getHtml($image, $size, $width, $height);
    }

    public function getUrl(string $image, string $size = 'original'): string
    {
        return $this->getHelper()->getUrl($image, $size);
    }

    public function getName(): string
    {
        return 'tmdb_extension';
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setHelper(ImageHelper $helper): self
    {
        $this->helper = $helper;

        return $this;
    }

    public function getHelper(): ?ImageHelper
    {
        if ($this->helper) {
            return $this->helper;
        }

        $this->helper = new ImageHelper($this->repository->load());

        return $this->helper;
    }
}
