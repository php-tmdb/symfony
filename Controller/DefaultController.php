<?php

namespace Wtfz\TmdbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $movie = $this->get('wtfz_tmdb.movie_repository')->load(13);

        return $this->render('WtfzTmdbBundle:Default:index.html.twig', array('movie' => $movie));
    }

    public function tvAction()
    {
        $tv = $this->get('wtfz_tmdb.tv_repository')->load(1396);

        return $this->render('WtfzTmdbBundle:Tv:index.html.twig', array('tv' => $tv));
    }
}
