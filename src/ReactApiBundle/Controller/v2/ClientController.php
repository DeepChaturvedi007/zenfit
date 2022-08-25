<?php

namespace ReactApiBundle\Controller\v2;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ReactApiBundle\Controller\Controller as sfController;
use ReactApiBundle\Services\AuthService;
use AppBundle\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/v2/client")
 */
class ClientController extends sfController
{
    private AuthService $authService;

    public function __construct(
        AuthService $authService,
        EntityManagerInterface $em,
        ClientRepository $clientRepository
    ) {
        $this->authService = $authService;
        parent::__construct($em, $clientRepository);
    }

    /**
     * @Method({"POST"})
     * @Route("/measuring")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function measuringAction(Request $request)
    {
        $client = $this->requestClient($request);
        $input = $this->requestInput($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (false === in_array((int) $input->measuring, [1, 2], true)) {
            return new JsonResponse([
                'message' => 'Invalid measuring unit type.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $client->setMeasuringSystem((int) $input->measuring);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Cannot update measuring, try again.',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($this->authService->getClientData($client));
    }
}
