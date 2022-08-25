<?php

namespace PlanBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("", name="plansOverview")
     */
    public function indexAction()
    {
        return $this->render('@Plan/default/index.html.twig');
    }
}
