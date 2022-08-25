<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Lead;
use AppBundle\Services\ClientImageService;
use AppBundle\Services\ClientService;
use AppBundle\Services\PaymentService;
use AppBundle\Services\QueueService;
use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ClientBundle\Transformer\ClientTransformer;
use AppBundle\Security\CurrentUserFetcher;

#[Route("/api/client")]
class ApiController extends Controller
{
    public function __construct(
        private EntityManagerInterface $em,
        private ClientService $clientService,
        private ClientImageService $clientImageService,
        private ValidationService $validationService,
        private PaymentService $paymentService,
        private QueueService $queueService,
        TokenStorageInterface $tokenStorage,
        private ClientTransformer $clientTransformer,
        private CurrentUserFetcher $currentUserFetcher
    ) {
        parent::__construct($em, $tokenStorage);
    }

    #[Route("/add", name: "addClient", methods: "POST")]
    public function addClientAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $json = $request->getContent();
        $res = json_decode($json);

        $name = $res ? $res->clientName : $request->request->get('clientName');
        $email = $res ? $res->clientEmail : $request->request->get('clientEmail');
        $tags = $res ? $res->tags : $request->request->get('tags');

        try {
            $validationService = $this->validationService;
            $validationService->checkEmptyString($name);
            $validationService->checkEmail($email);
            $clientService = $this->clientService;
            $clientService->clientEmailExist($email);

            $client = $clientService
                ->addClient($name, $email, $user, null, true);

            //add tags to client
            $clientService->addTags($client, $tags, true);

            if ($currentUser->isAssistant()) {
                $clientService->addTags($client, [$currentUser->getFirstName()]);
            }

            $queue = $this
                ->queueService
                ->createClientCreationEmailQueueEntity($client);

            $this->em->flush();

            return new JsonResponse([
                'client' => $client->getId(),
                'queue' => $queue->getId(),
                'clientName' => $client->getName(),
                'client_data' => [
                    'id' => $client->getId(),
                    'name' => $client->getFirstName(),
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route("/status/update", methods: "POST")]
    public function updateClientStatusAction(Request $request): JsonResponse
    {
        $clients = (array) $request->query->get('clients');
        $status = (string) $request->query->get('status');

        foreach ($clients as $client) {
            $client = $this
                ->getEm()
                ->getRepository(Client::class)
                ->find($client);

            if ($client === null) {
                throw new NotFoundHttpException('Client not found');
            }

            if ($status === 'activate') {
                $client
                    ->setActive(true)
                    ->setAccessApp(true);
            } else {
                $this->clientService->deactivateClients([$client->getId()]);
            }
        }

        $this->em->flush();

        if ($status === "activate") {
            return new JsonResponse('The clients were activated!');
        } else {
            return new JsonResponse('The clients were deactivated!');
        }
    }

    #[Route("/settings/set-client-settings", name: "setClientSettings", methods: "POST")]
    public function setClientSettingsAction(Request $request): JsonResponse
    {
        $service = $this->clientService;
        $questionnaire = $request->request->get('questionnaire');
        $trackProgress = $request->request->get('trackProgress');
        $startDate = (string) $request->request->get('startDurationTime');
        $duration = $request->request->get('duration');
        $dayTrackProgress = $request->request->get('dayTrackProgress');
        $clientId = $request->request->get('client');
        $lead = $request->request->get('lead');
        $queue = $request->request->get('queue');
        $modal = $request->request->get('modal');
        $payment = $request->request->get('payment');
        $isQuestionnaire = $questionnaire ? true : false;
        $client = $this->em->getRepository(Client::class)->find($clientId);

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if (!$client) {
            return new JsonResponse([
                'error' => 'No client found. Please try to refresh your page.'
            ], 422);
        }
        if ($trackProgress) {
            $service->setClientTrackProgressDay($dayTrackProgress, $client);
        }

        if ($modal == 'addClient') {
            //set client start date + duration
            $startDate = !$startDate ? new \DateTime() : new \DateTime($startDate);
            $service->setClientDuration($startDate, $duration, $client);
        }

  	    if ($payment) {
    		    $currency = $request->request->get('currency');
    		    $signUpFee = $request->request->get('signUpFee');
    		    $monthlyAmount = $request->request->get('monthlyAmount');
    		    $periods = $request->request->get('periods');
            $terms = $request->request->get('terms');
            $startPaymentDate = (string) $request->request->get('startPaymentDate');
            $startPaymentTs = null;

            if ($startPaymentDate && $startPaymentDate !== 'null') {
                try {
                    $startPaymentDate = new \DateTime($startPaymentDate);
                    $startPaymentTs = $startPaymentDate->getTimestamp();
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'error' => 'Invalid start payment date format.'
                    ], 422);
                }
            }
            $delayUpfront = $startPaymentTs && !$request->request->get('chargeUpfrontImmediately') ? true : false;

  	        try {
                $paymentService = $this->paymentService;
                $paymentService->validatePaymentInput($signUpFee, $monthlyAmount, $periods, $startPaymentTs);

                $payment = $paymentService->generatePayment(
                    $client,
                    (string) $currency,
                    (float) $signUpFee,
                    (float) $monthlyAmount,
                    (int) $periods,
                    $startPaymentTs,
                    $terms,
                    (bool) $delayUpfront,
                    $currentUser->isAssistant() && $currentUser->getUserStripe() !== null ? $currentUser->getUserStripe()->getFeePercentage() : null,
                    $currentUser->isAssistant() && $currentUser->getUserStripe() !== null ? $currentUser : null
                );

            } catch (\Exception $e) {
                return new JsonResponse([
                  'error' => $e->getMessage()
                ], 422);
            }
  	    }

        if ($lead) {
            $lead = $this
                ->em
                ->getRepository(Lead::class)
                ->find($lead);

            if ($lead !== null) {
                if ($payment instanceof Payment) {
                    $lead
                        ->setPayment($payment)
                        ->setStatus(Lead::LEAD_PAYMENT_WAITING);
                } else {
                    $lead->setStatus(Lead::LEAD_WON);
                }
            }
        }

        if($queue) {
            $queue = $this
                ->em
                ->getRepository(Queue::class)
                ->find($queue);
        } else {
            $queue = $this
                ->queueService
                ->createClientCreationEmailQueueEntity($client);
        }

        if ($queue === null) {
            throw new NotFoundHttpException('No queue found');
        }

        //prepare events we wish to dispatch
        $events = [Event::TRAINER_CREATE_MEAL_PLAN, Event::TRAINER_CREATE_WORKOUT_PLAN];

        if(!$client->getPassword()) {
            //client is new
            $events[] = Event::INVITE_PENDING;
            $events[] = Event::NEED_WELCOME;
        }

        if ($payment) {
            $queue->setPayment($payment);
            $events[] = Event::PAYMENT_PENDING;
        }

        if ($isQuestionnaire) {
            $queue->setSurvey(true);
            $events[] = Event::QUESTIONNAIRE_PENDING;
        }

        $service->dispatchClientEvents($client, $events);
        $client
            ->setDeleted(false)
            ->setAccessApp(true);

        $this->em->flush();

        return new JsonResponse([
            'payment'   => $payment ? [
                'id' => $payment->getId(),
                'datakey' => $payment->getDatakey()
            ] : null,
            'queue'     => [
                'id' => $queue->getId(),
                'payment' => $queue->getPayment() ? true : false,
                'datakey' => $queue->getDatakey(),
                'createdAt' => $queue->getCreatedAt()
            ],
            'name' => $client->getName(),
            'id'   => $client->getId(),
            'firstName' => $client->getFirstName(),
            'email' => $client->getEmail(),
            'trainer' => [
                'id' => $client->getUser()->getId(),
                'name' => $client->getUser()->getName()
            ]
        ]);
    }

    #[Route("/submitClientInfo/{client}", methods: "POST")]
    public function submitClientInfo(Client $client, Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if ($client !== null && !$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $body = $this->requestInput($request);
            $this
                ->clientService
                ->submitClientInfo($body, $client);
            return new JsonResponse($this->clientTransformer->transformForList($client));
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    #[Route("/updateClientInfo/{client}", name: "updateClientInfo", methods: "POST")]
    public function updateClientInfoAction(Client $client, Request $request): JsonResponse
    {
        $clientService = $this->clientService;

        try {
            $clientService->updateClientInformation($request, $client);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }

        $endDate = $client->getEndDate();
        $startDate = $client->getStartDate();

        return new JsonResponse([
            'status' => 'success',
            'client' => [
                'photo' => $client->getPhoto(),
                'upload' => $request,
                'duration' => $client->getDuration(),
                'endDate' => $endDate ? $endDate->format('d M Y') : null,
                'startDate' => $startDate ? $startDate->format('d M Y') : null
            ]
        ]);

    }

    #[Route("/uploadImg/{client}", name: "uploadImg", methods: "POST")]
    public function uploadImageAction(Request $request, Client $client): JsonResponse
    {
        try {
            /** @var ?UploadedFile $filesFront */
            $filesFront = $request->files->get('front-img');
            /** @var ?UploadedFile $filesBack */
            $filesBack = $request->files->get('back-img');
            /** @var ?UploadedFile $filesSide */
            $filesSide = $request->files->get('side-img');
            /** @var ?UploadedFile $filesProfile */
            $filesProfile = $request->files->get('profile-img');

            if (!isset($filesFront) && !isset($filesSide) && !isset($filesBack) && !isset($filesProfile)) {
                return new JsonResponse(array(
                    'message' => 'You have to select at least one image.'
                ), 400);
            }

            $date = new \DateTime($request->request->get('date'));
            $clientImageService = $this->clientImageService;

            if (isset($filesFront)) {
                $clientImageService->upload($filesFront, $date, $client);
            }
            if (isset($filesBack)) {
                $clientImageService->upload($filesBack, $date, $client);
            }
            if (isset($filesSide)) {
                $clientImageService->upload($filesSide, $date, $client);
            }
            if (isset($filesProfile)) {
                $url = $this
                    ->clientService
                    ->uploadClientPhoto($filesProfile, $client);
                $client->setPhoto($url);
            }

            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['msg' => 'Success.']);
    }
}
