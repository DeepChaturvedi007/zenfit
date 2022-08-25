<?php

namespace Zenfit\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function loginAction()
    {
        return $this->render('@App/default/login.html.twig');
    }

    public function registerAction()
    {
        return $this->render('@App/default/register.html.twig');
    }
}
