<?php declare(strict_types=1);

namespace TrainerBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Security\CurrentUserFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        private CurrentUserFetcher $currentUserFetcher,
        TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct($em, $tokenStorage);
    }

    #[Route("/login", name: "authLogin", methods: ["GET"])]
    public function login(Request $request): Response
    {
        if ($this->currentUserFetcher->isLoggedIn()) {
            return new RedirectResponse($this->generateUrl('dashboardOverview'));
        }

        $view = 'login';
        return $this->render('@ZenfitUser/Security/auth.html.twig', compact('view'));
    }

    #[Route("/sign-up", name: "signup", methods: ["GET"])]
    public function signup(Request $request): Response
    {
        $view = 'signup';
        return $this->render('@ZenfitUser/Security/auth.html.twig', compact('view'));
    }

    #[Route("/new-password", name: "authNewPassword", methods: ["GET"])]
    public function newPassword(Request $request): Response
    {
        $view = 'newPassword';
        $datakey = $request->query->get('datakey');
        return $this->render('@ZenfitUser/Security/auth.html.twig', compact('view', 'datakey'));
    }
}
