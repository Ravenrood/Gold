<?php

namespace Gold\Bundle\AnalysisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GoldAnalysisBundle:Default:index.html.twig');
    }
}
