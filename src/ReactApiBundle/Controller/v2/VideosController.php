<?php

namespace ReactApiBundle\Controller\v2;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ReactApiBundle\Controller\Controller as sfController;
use VideoBundle\Transformer\VideoClientTransformer;
use League\Fractal\Manager;
use League\Fractal;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\VideoClientRepository;

/**
 * @Route("/v2/videos")
 */
class VideosController extends sfController
{
    private VideoClientRepository $videoClientRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        VideoClientRepository $videoClientRepository
    ) {
        $this->videoClientRepository = $videoClientRepository;
        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $videos = $this
            ->videoClientRepository
            ->findByClient($client);

        return new JsonResponse($videos);
    }

    /**
     * @Route("/seen", methods={"PATCH"})
     */
    public function seenAction(Request $request): JsonResponse
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $this
                ->videoClientRepository
                ->markAsSeenByClient($client);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([]);
    }
}
