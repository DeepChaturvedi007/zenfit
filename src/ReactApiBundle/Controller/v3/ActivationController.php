<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Repository\QueueRepository;
use AppBundle\Services\ClientService;
use ClientBundle\Transformer\ClientTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ReactApiBundle\Controller\Controller as sfController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Repository\ClientRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;

#[Route("/activation")]
class ActivationController extends sfController
{
    public function __construct(
        protected EntityManagerInterface $em,
        private ClientRepository $clientRepository,
        private QueueRepository $queueRepository,
        private ClientService $clientService,
        private ClientTransformer $clientTransformer,
        private EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($em, $clientRepository);
    }

    #[Route("/submit", methods: "POST")]
    public function postClientInfo(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $datakey = $body->datakey;
            $isQuestionnaire = $body->isQuestionnaire;

            if ($datakey === null) {
                throw new NotFoundHttpException('No datakey found');
            }

            $queue = $this
                ->queueRepository
                ->findOneByDatakey($datakey);

            if ($queue === null) {
                throw new NotFoundHttpException('No queue entity found');
            }

            $client = $this
                ->clientRepository
                ->find($body->client);

            if ($client === null) {
                throw new NotFoundHttpException('No client entity found');
            }

            $this
                ->clientService
                ->submitClientInfo((array) $body, $client);

            if ($isQuestionnaire) {
                //dispatch event if client answered questionnaire
                $client->setAnsweredQuestionnaire(true);
                $this->em->flush();
                $eventName = Event::FILLED_OUT_SURVEY;
            } else {
                //dispatch event if client created login
                $eventName = Event::CREATED_LOGIN;
            }

            $event = new ClientMadeChangesEvent($client, $eventName);
            $this
                ->eventDispatcher
                ->dispatch($event, $eventName);

            return new JsonResponse($this->clientTransformer->transformForList($client));
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
