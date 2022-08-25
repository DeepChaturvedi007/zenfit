<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\DocumentClient;
use AppBundle\Entity\User;
use AppBundle\Repository\DocumentRepository;
use AppBundle\Services\DocumentService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aws\Exception\AwsException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/dashboard")
 */
class DocumentController extends Controller
{
    private DocumentService $documentService;

    public function __construct(
        DocumentService $documentService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->documentService = $documentService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/clientDocuments/{client}", name="clientDocuments")
     * @param Client $client
     * @return RedirectResponse|Response
     */
    public function clientDocumentsAction(Client $client)
    {
        $em = $this->getEm();

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        abort_unless(is_owner($user, $client), 403, 'This client does not belong to you.');

        $documents = $em
            ->getRepository(Document::class)
            ->getDocumentsByUser($user);

        $clientDocuments = $em
            ->getRepository(DocumentClient::class)
            ->findByClient($client);

        $documentsArray = [];

        /** @var Document $document */
        foreach($documents as $document) {
            $documentsArray[$document->getId()] = array(
                'id' => $document->getId(),
                'name' => $document->getName(),
                'fileName' => $document->getFileName(),
                'exists' => false
            );
        }

        foreach($clientDocuments as $clientDocument) {
            foreach(array_keys($documentsArray) as $key) {
                if(array_key_exists('id', $clientDocument) && $key === $clientDocument['id']) {
                    $documentsArray[$key]['exists'] = true;
                }
            }
        }

  	    $demoClient = $client->getDemoClient();

        $unreadClientMessagesCount = $user->unreadMessagesCount($client);

        return $this->render('@App/default/clientDocuments.html.twig', array(
            'client' => $client,
            'documents' => $documentsArray,
            'clientDocuments' => $clientDocuments,
            'demoClient' => $demoClient,
            'unreadClientMessagesCount' => $unreadClientMessagesCount
        ));
    }

    /**
     * @Route("/addDocumentToClient/{document}/{client}", name="addDocumentToClient")
     * @param Document $document
     * @param Client $client
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addDocumentToClientAction(Document $document,Client $client)
    {
        $this->documentService->linkDocumentAndClient($document, $client);

        return $this->redirectToRoute('clientDocuments', [ 'client' => $client->getId() ]);
    }

    /**
     * @Route("/deleteDocumentToClient/{document}/{client}", name="deleteDocumentToClient")
     * @param Document $document
     * @param Client $client
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteDocumentToClientAction(Document $document, Client $client)
    {
        abort_unless(is_owner($client->getUser(), $client), 403, 'This client does not belong to you.');

        $this->documentService->unlinkDocumentAndClient($document, $client);

        return $this->redirectToRoute('clientDocuments',array(
            'client' => $client->getId()
        ));
    }

    /**
     * @Route("/documentOverview", name="documentOverview")
     */
    public function documentOverviewAction()
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        /** @var DocumentRepository $documentsRepository */
        $documentsRepository = $em->getRepository(Document::class);
        $documents = $documentsRepository->getDocumentsByUser($user);

        $reducerFn = function($sum, $doc) {
            /** @var Document $doc */
            if ((bool) $doc->getDemo()) {
                $sum += 1;
            }
            return $sum;
        };
        $count = count($documents);
        $demoCount = array_reduce($documents, $reducerFn, 0);
        $showCreateBox = $count === 0 || $demoCount === $count;

        return $this->render('@App/default/user/documents/index.html.twig', array(
            'documents' => $documents,
            'showCreateBox' => $showCreateBox,
        ));
    }

    /**
     * @Route("/uploadDocument/{client}", name="uploadDocument", defaults={"client" = null})
     * @param Request $request
     * @param Client|null $client
     * @return RedirectResponse
     */
    public function uploadDocumentAction(Request $request, Client $client = null)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $id                     = $request->request->get('id', null);
        /** @var ?UploadedFile $file */
        $file                   = $request->files->get('document');
        $title                  = $request->request->get('title');
        $comment                = $request->request->get('comment');
        $assignTo               = (string) $request->request->get('assignTo');

        $asDeleted = $client ? true : false;

        try {
            $documentService = $this->documentService;
            $document = $documentService->updateOrCreate($user, (int) $id, $file, $title, $comment, $asDeleted);
            if($client) {
                $documentService->linkDocumentAndClient($document, $client);
            } else {
                $documentService->assignDocumentToClients($assignTo, $document, $user);
            }
        } catch (AwsException $e) {
            echo $e->getMessage();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $redirectRoute = $client ? 'clientDocuments' : 'documentOverview';
        $redirectParams = $client ? ['client' => $client->getId()] : [];

        return $this->redirectToRoute($redirectRoute, $redirectParams);
    }

    /**
     * @Route("/deleteDocument/{document}", name="deleteDocument")
     * @param Request $request
     * @param Document $document
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteDocument(Request $request, Document $document)
    {
        $shouldDeleteEverywhere = $request->query->getBoolean('everywhere', false);

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if($document->getUser()->getId() !== $user->getId()) {
            return $this->redirectToRoute('documentOverview');
        }

        if($shouldDeleteEverywhere) {
            /** @var DocumentClient[] $documentClients */
            $clientDocuments = $this
                ->getEm()
                ->getRepository(DocumentClient::class)
                ->findByDocument($document);
            foreach ($clientDocuments as $clientDocument) {
                $this->getEm()->remove($clientDocument);
            }
        }

        $this->documentService->delete($document);
        return $this->redirectToRoute('documentOverview');
    }
}
