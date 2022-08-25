<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Repository\DocumentRepository;
use AppBundle\Repository\DocumentClientRepository;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\DocumentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Transformer\DocumentTransformer;

/**
 * @Route("/docs/api")
 */
class DocumentsController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
        private CurrentUserFetcher $currentUserFetcher,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private DocumentRepository $documentRepository,
        private DocumentClientRepository $documentClientRepository,
        private DocumentTransformer $documentTransformer,
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getDocs(Request $request): JsonResponse
    {
        $currentUser = $this->currentUserFetcher->getCurrentUser();

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $documents = $this
            ->documentRepository
            ->findByUser($user);

        $serialized = collect($documents)->map(function(Document $doc) {
            return $this->documentTransformer->transform($doc);
        });

        return new JsonResponse($serialized->toArray());
    }

    /**
     * @Route("/{client}", methods={"GET"})
     */
    public function getClientDocs(Request $request, Client $client): JsonResponse
    {
        $currentUser = $this->currentUserFetcher->getCurrentUser();

        if (!$this->clientBelongsToUser($client, $currentUser)) {
            throw new AccessDeniedHttpException('You don\'t have access to this client');
        }

        $documents = $this
            ->documentClientRepository
            ->findByClient($client);

        return new JsonResponse($documents);
    }

    /**
     * @Route("/{client}/{document}", methods={"POST"})
     */
    public function addDocToClient(Request $request, Client $client, Document $document): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->documentService->linkDocumentAndClient($document, $client);
            return new JsonResponse($this->documentTransformer->transform($document));
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("/{client}/{document}", methods={"DELETE"})
     */
    public function deleteClientDoc(Request $request, Client $client, Document $document): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->documentService->unlinkDocumentAndClient($document, $client);
            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("/upload", methods={"POST"})
     */
    public function uploadDoc(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        $title = $request->request->get('title');
        $comment = $request->request->get('comment');
        $file = $request->files->all()[0];

        try {
            $document = $this
                ->documentService
                ->updateOrCreate($user, null, $file, $title, $comment);
            return new JsonResponse($this->documentTransformer->transform($document));
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        }
    }
}
