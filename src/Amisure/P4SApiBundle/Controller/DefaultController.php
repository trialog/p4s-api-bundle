<?php

namespace Amisure\P4SApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AmisureP4SApiBundle:Default:index.html.twig', array('name' => $name));
    }
}
