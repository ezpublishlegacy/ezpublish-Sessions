<?php

namespace ACME\SessionsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ACMESessionsBundle:Default:index.html.twig', array('name' => $name));
    }
}
