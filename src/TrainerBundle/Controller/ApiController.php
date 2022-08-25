<?php

namespace TrainerBundle\Controller;

use AppBundle\Entity\LeadTag;
use AppBundle\Entity\Payment;
use AppBundle\Entity\VideoTag;
use AppBundle\Repository\ClientTagRepository;
use AppBundle\Repository\DefaultMessageRepository;
use AppBundle\Services\DefaultMessageService;
use AppBundle\Services\ErrorHandlerService;
use AppBundle\Services\QueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Client;
use AppBundle\Entity\Plan;
use AppBundle\Entity\Lead;
use AppBundle\Entity\Queue;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use AppBundle\Transformer\DefaultMessageTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/trainer")
 */
class ApiController extends Controller
{
    private DefaultMessageService $defaultMessageService;
    private ErrorHandlerService $errorHandlerService;
    private QueueService $queueService;
    private string $appHostname;
    private UrlGeneratorInterface $urlGenerator;
    private ClientTagRepository $clientTagRepository;
    private DefaultMessageRepository $defaultMessageRepository;
    private DefaultMessageTransformer $defaultMessageTransformer;

    public function __construct(
        DefaultMessageService $defaultMessageService,
        ErrorHandlerService $errorHandlerService,
        QueueService $queueService,
        ClientTagRepository $clientTagRepository,
        DefaultMessageRepository $defaultMessageRepository,
        EntityManagerInterface $em,
        string $appHostname,
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage,
        DefaultMessageTransformer $defaultMessageTransformer
    ) {
        $this->defaultMessageService = $defaultMessageService;
        $this->errorHandlerService = $errorHandlerService;
        $this->queueService = $queueService;
        $this->appHostname = $appHostname;
        $this->urlGenerator = $urlGenerator;
        $this->clientTagRepository = $clientTagRepository;
        $this->defaultMessageRepository = $defaultMessageRepository;
        $this->defaultMessageTransformer = $defaultMessageTransformer;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/clients", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getClients(Request $request)
    {
        $em = $this->getEm();
        $response = [];

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $tags = explode(',', $request->query->get('tags', ''));
        $userToGetClientsFrom = $user;
        if ($user->isAssistant()) {
            $firstName = $user->getFirstName();
            if ($firstName === null) {
                throw new \RuntimeException("User {$user->getId()} has no firstName filled in");
            }
            $userToGetClientsFrom = $user->getGymAdmin();
            $tags = [$firstName];
        }

        $query = trim($request->query->get('q', ''));
        $response['clients'] = $em
            ->getRepository(Client::class)
            ->getClientsByUser($userToGetClientsFrom, $query, $tags);

        return new JsonResponse($response);
    }

    /**
     * @Route("/set-default-message", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setDefaultMessage(Request $request)
    {
        // Trying to extract json body if it present
        $body = json_decode($request->getContent(), false);

        $message        = isset($body->textarea)    ? $body->textarea   : $request->request->get('textarea');
        $type           = isset($body->type)        ? $body->type       : $request->request->get('type');
        $title          = isset($body->title)       ? $body->title      : $request->request->get('title');
        $subject        = isset($body->subject)     ? $body->subject    : $request->request->get('subject');

        try {
            $user = $this->getUser();
            if ($user === null) {
                throw new AccessDeniedHttpException();
            }
            $defaultMessage = $this
                ->defaultMessageService
                ->create($user, $message, $type, $title, $subject);

            return new JsonResponse([
                'reason' => "Message with title: '$title' saved.",
                'data' => $this->defaultMessageTransformer->transform($defaultMessage)
            ]);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @Route("/update-default-message/{id}", methods={"POST"})
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateDefaultMessage(int $id, Request $request)
    {
        // Trying to extract json body if it present
        $body = json_decode($request->getContent(), false);

        $message        = isset($body->textarea)    ? $body->textarea   : $request->request->get('textarea');
        $type           = isset($body->type)        ? $body->type       : $request->request->get('type');
        $title          = isset($body->title)       ? $body->title      : $request->request->get('title');
        $subject        = isset($body->subject)     ? $body->subject    : $request->request->get('subject');

        try {
            $user = $this->getUser();
            if ($user === null) {
                throw new AccessDeniedHttpException();
            }
            $defaultMessage = $this
                ->defaultMessageRepository
                ->find($id);

            if (!$defaultMessage) {
                throw new \Exception('Default message not found');
            }

            $defaultMessage = $this
                ->defaultMessageService
                ->update($user, $defaultMessage, $message, $type, $title, $subject);

            return new JsonResponse([
                'reason' => "Message with title: '$title' is updated.",
                'data' => $this->defaultMessageTransformer->transform($defaultMessage)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/delete-default-message/{id}", methods={"GET"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteDefaultMessage(int $id)
    {
        $em = $this->getEm();

        try {
            $defaultMessage = $this
                ->defaultMessageRepository
                ->find($id);

            if (!$defaultMessage) {
                throw new \Exception('Default message not found');
            }

            if ($defaultMessage->getUser() !== $this->getUser()) {
                throw new AccessDeniedHttpException('Default message doesn\'t belong to current user');
            }

            $type = $defaultMessage->getType();

            $em->remove($defaultMessage);
            $em->flush();

            return new JsonResponse([
                'reason' => "Message with id: '$id' is deleted.",
                'data' => ['id' => $id, 'type' => $type],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /** @Route("/get-default-message/{id}", methods={"GET"}) */
    public function getDefaultMessage(int $id): JsonResponse
    {
        $defaultMessage = $this
            ->defaultMessageRepository
            ->find($id);

        if ($defaultMessage !== null) {
            return new JsonResponse($this->defaultMessageTransformer->transform($defaultMessage));
        }

        return new JsonResponse(['error' => 'Default message not found']);
    }

    /**
     * @Route("/get-default-messages/{type}/{client}/{locale}/{datakey}", methods={"GET"})
     * @Route("/get-default-messages/{type}/{client}/{locale}", methods={"GET"})
     * @Route("/get-default-messages/{type}/{client}", methods={"GET"})
     */
    public function getDefaultMessages(int $type, Client $client, ?string $locale = null, ?string $datakey = null): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('You must be logged in');
        }

        if ($locale === null) {
            $language = $client->getUser()->getLanguage();
            if ($language !== null) {
                $locale = $language->getLocale();
            }
        }

        $placeholders = $this
            ->defaultMessageService
            ->getPlaceholders($type, $client, $datakey);

        $defaultMessages = $this
            ->defaultMessageRepository
            ->getByUserAndType($currentUser, $type, $placeholders, $locale);

        return new JsonResponse([
            'defaultMessages' => $defaultMessages
        ]);
    }

    /**
     * @Route("/get-tags-by-user", methods={"GET"})
     * @return JsonResponse
     */
    public function getTagsByUser()
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([]);
        }

        $repo = $this->clientTagRepository;

        $assistantsTags = [];
        if (!$user->isAssistant()) {
            $assistants = $user->getAllAssistants();
            foreach ($assistants as $assistant) {
                $assistantsTags[] = $assistant->getFirstName();
            }
        }

        $tags = collect($repo->getAllTagsByUser($user))
            ->map(function($tag) {
                return $tag['title'];
            })
            ->toArray();

        return new JsonResponse([
            'tags' => collect($assistantsTags)->concat($tags)->unique()->values()->toArray()
        ]);
    }

    /**
     * @Route("/get-lead-tags-by-user", methods={"GET"})
     */
    public function getLeadTagsByUser(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $repo = $this->getEm()->getRepository(LeadTag::class);

        return new JsonResponse([
            'tags' => $repo->getAllTagsByUser($user)
        ]);
    }

    /**
     * @Route("/get-video-tags-by-user", methods={"GET"})
     */
    public function getVideoTagsByUser(): JsonResponse
    {
        $repo = $this->getEm()->getRepository(VideoTag::class);
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }
        return new JsonResponse([
            'tags' => $repo->getAllUniqueTagTitlesByUser($user),
        ], 200);
    }

    /**
     * @Route("/lead/{id}", methods={"GET"})
     */
    public function getLead(Lead $lead): JsonResponse
    {
        $view = $this->renderView('@App/components/clientFields/index.html.twig', array(
            'client' => $lead->getClient()
        ));

        return new JsonResponse([
            'view' => $view
        ], 200);
    }

    /**
     * @Route("/payment/{queue}", methods={"GET"})
     * @param Queue $queue
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentEmail(Queue $queue, Request $request)
    {
        $payment = $queue->getPayment();
        if (!$payment) {
            $em = $this->getEm();
            $payment = $em->getRepository(Payment::class)->findOneBy([
                'client' => $queue->getClient()
            ]);
            if ($payment === null) {
                throw new NotFoundHttpException('No Payment');
            }
            $queue->setPayment($payment);
            $em->flush();
        }

        $checkout = $this->appHostname .
            $this->urlGenerator->generate('zenfit_stripe_checkout', array(
                    'key' => $payment->getDatakey()
                )
            );

        $view = $this->renderView('@App/components/emailEditors/paymentEditor.html.twig', array(
            'client' => $queue->getClient(),
            'queue' => $queue,
            'checkout' => $checkout,
            'reload' => $request->query->get('reload')
        ));

        return new JsonResponse([
            'view' => $view
        ], 200);
    }

    /**
     * @Route("/welcome/{queue}", methods={"GET"})
     * @param Queue $queue
     * @param Request $request
     * @return JsonResponse
     */
    public function welcomeEmail(Queue $queue, Request $request)
    {
        $url = $this->appHostname .
            $this->urlGenerator->generate('clientActivation', array(
                    'datakey' => $queue->getDatakey()
                )
            );

        $view = $this->renderView('@App/components/emailEditors/welcomeEditor.html.twig', array(
            'client' => $queue->getClient(),
            'queue' => $queue,
            'url' => $url,
            'reload' => $request->query->get('reload')
        ));

        return new JsonResponse([
            'view' => $view
        ], 200);
    }

    /**
     * @Route("/plans-ready/{plan}", methods={"GET"})
     */
    public function plansReadyEmail(Plan $plan): JsonResponse
    {
        $view = $this->renderView('@App/components/emailEditors/plansReadyEditor.html.twig', array(
            'plan' => $plan
        ));

        return new JsonResponse([
            'view' => $view
        ], 200);
    }

    /**
     * @Route("/send-email-to-client", name="send-email-to-client", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $message = (string) $request->request->get('message');
        $subject = (string) $request->request->get('subject');
        $to = (string) $request->request->get('to');
        $type = $request->request->get('type');
        $reload = $request->request->get('reload');
        $queue = $request->request->get('queue');
        $plan = $request->request->get('plan');
        $em = $this->getEm();

        if (!$message || !$subject || !$to) {
            return new JsonResponse([
                'success' => false,
                'reason' => 'You are missing some fields.'
            ], 422);
        }

        if (!str_contains($message, 'http') && !$plan) {
            $queue = $em->getRepository(Queue::class)->find($queue);
            if ($queue === null) {
                throw new NotFoundHttpException('Queue not found');
            }
            $payment = $queue->getPayment();
            if ($payment) {
                $url = $this->appHostname .
                    $this->urlGenerator->generate('zenfit_stripe_checkout', array(
                            'key' => $payment->getDatakey()
                        )
                    );
            } else {
                $url = $this->appHostname .
                    $this->urlGenerator->generate('clientActivation', array(
                            'datakey' => $queue->getDatakey()
                        )
                    );
            }

            $link = "<a href=$url>$url</a>";
            $message = $message . $link;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'success' => false,
                'reason' => 'Invalid email address'
            ], 422);
        }

        if ($queue) {
            $queue = $em
                ->getRepository(Queue::class)
                ->find($queue);
            if ($queue === null) {
                throw new NotFoundHttpException('No queue found');
            }
        } else {
            $plan = $em
                ->getRepository(Plan::class)
                ->find($plan);
            if ($plan === null) {
                throw new NotFoundHttpException('Plan not found');
            }

            $plan->setContacted(true);

            $client = $plan->getClient();
            if ($client === null) {
                throw new \RuntimeException('No Client');
            }
            $queue = $this
                ->queueService
                ->createClientCreationEmailQueueEntity($client);
        }

        $queue
            ->setMessage($message)
            ->setSubject($subject)
            ->setStatus(Queue::STATUS_PENDING)
            ->setEmail($to)
            ->setCreatedAt(new \DateTime());

        $queueClient = $queue->getClient();
        if ($queueClient === null) {
            throw new \RuntimeException('No Client');
        }
        $queueClient
            ->setEmail($to)
            ->setDeleted(false);

        $em->flush();

        return new JsonResponse([
            'date' => new \DateTime(),
            'success' => true,
            'reload' => filter_var($reload, FILTER_VALIDATE_BOOLEAN)
        ]);

    }
}
