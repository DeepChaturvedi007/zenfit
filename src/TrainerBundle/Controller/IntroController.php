<?php

namespace TrainerBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Services\UserSubscriptionService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/intro")
 */
class IntroController extends Controller
{
    private string $stripePublishableKey;
    private SessionInterface $session;
    private UserSubscriptionService $userSubscriptionService;

    public function __construct(
        EntityManagerInterface $em,
        UserSubscriptionService $userSubscriptionService,
        SessionInterface $session,
        string $stripePublishableKey,
        TokenStorageInterface $tokenStorage
    ) {
        $this->stripePublishableKey = $stripePublishableKey;
        $this->session = $session;
        $this->userSubscriptionService = $userSubscriptionService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/", name="intro")
     *
     * @return RedirectResponse|Response
     */
    public function introAction(Request $request)
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }
        if ($user->getActivated()) {
            return $this->redirectToRoute('dashboardOverview');
        }

        $userSubscription = $user->getUserSubscription();

        //7 day trial by default
        //check if user should be charged immediately
        //eg. has cookie nt = no-trial
        $trialUntil = Carbon::now()->addDays(7)->toDate();
        $cookies = $request->cookies;
        if ($cookies->has('nt')) {
            $trialUntil = null;
        }

        return $this->render('@Trainer/default/intro.html.twig', [
            'stripeKey' => $this->stripePublishableKey,
            'userSubscription' => $userSubscription,
            'trialUntil' => $trialUntil
        ]);
    }

    /**
     * @Route("/success", name="success")
     */
    public function successAction()
    {
        return $this->redirectToRoute('intro');
    }

}
