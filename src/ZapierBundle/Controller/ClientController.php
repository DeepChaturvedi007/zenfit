<?php

namespace ZapierBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Services\LeadService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    private LeadService $leadService;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        LeadService $leadService,
    ) {
        $this->leadService = $leadService;
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Method({"POST"})
     * @Route("")
     */
    public function createClientAction(Request $request)
    {
        try {
            $user = $this->getUserFromRequest($request);

            $response = $this
                ->leadService
                ->submitSurvey($request, $user);

            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
