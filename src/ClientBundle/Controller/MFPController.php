<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Services\MyFitnessPalService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/mfp")
 */
class MFPController extends Controller
{
    private MyFitnessPalService $myFitnessPalService;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        MyFitnessPalService $myFitnessPalService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->myFitnessPalService = $myFitnessPalService;
        $this->urlGenerator = $urlGenerator;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/callback", name="mfpCallback")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function mfpCallbackAction(Request $request)
    {
        $errorMsg = null;
        $state = $request->query->get('state');
        $code = $request->query->get('code');

        if ($code && $state) {
            try {
                $this
                    ->myFitnessPalService
                    ->authMfpClient($state, $code)
                ;
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
            }
        } else {
            $errorMsg = 'Authorization failed. Code/State is empty.';
        }

        if ($errorMsg) {
            return new Response($errorMsg);
        } else {
            return new RedirectResponse($this->urlGenerator->generate('clientSettings'));
        }
    }
}
