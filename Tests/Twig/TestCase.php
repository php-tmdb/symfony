<?php
namespace Tmdb\SymfonyBundle\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmdb\SymfonyBundle\Tests\AppKernel;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class TestCase extends WebTestCase
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Create the kernel
     *
     * @param array $options
     * @return \Symfony\Component\HttpKernel\KernelInterface|AppKernel
     */
    protected static function createKernel(array $options = array())
    {
        return new AppKernel(isset($options['config']) ? $options['config'] : 'config.yml');
    }

    protected function setUp()
    {
        static::createClient();

        $this->twig = self::$kernel->getContainer()->get('twig');
    }

    protected function getTwig()
    {
        return $this->twig;
    }
}
