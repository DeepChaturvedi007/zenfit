<?php

namespace ChatBundle\Controller;

use AppBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DefaultController extends Controller
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->authorizationChecker = $authorizationChecker;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/overview", name="chatOverview")
     */
    public function indexAction()
    {
        $securityContext = $this->authorizationChecker;
        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
          return new RedirectResponse('/login');
        }

        return $this->render('@Chat/Default/index.html.twig');
    }
}
