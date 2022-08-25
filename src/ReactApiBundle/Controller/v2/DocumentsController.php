<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Entity\Document;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Repository\DocumentClientRepository;
use AppBundle\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Transformer\DocumentTransformer;

/**
 * @Route("/v2/documents")
 */
class DocumentsController extends sfController
{
    private DocumentClientRepository $documentClientRepository;
    private DocumentTransformer $documentTransformer;

    public function __construct(
        EntityManagerInterface $em,
        DocumentClientRepository $documentClientRepository,
        ClientRepository $clientRepository,
        DocumentTransformer $documentTransformer
    ) {
        $this->documentClientRepository = $documentClientRepository;
        $this->documentTransformer = $documentTransformer;
        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null,JsonResponse::HTTP_UNAUTHORIZED);
        }

        $documents = $this
            ->documentClientRepository
            ->findByClient($client);

        if (!$client->hasBeenActivated()) {
            $documents = [];
        }

        return new JsonResponse($documents);
    }
}
