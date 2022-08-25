<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Entity\ProgressFeedback;
use AppBundle\Entity\User;
use AppBundle\Services\ClientService;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Services\LeadService;
use AppBundle\Services\ErrorHandlerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Entity\Lead;
use AppBundle\Repository\ClientRepository;
use ReactApiBundle\Services\AuthService;

/**
 * @Route("/support")
 */
class SupportController extends sfController
{
    private LeadService $leadService;
    private ChatService $chatService;
    private ClientService $clientService;
    private ErrorHandlerService $errorHandlerService;
    private AuthService $authService;

    public function __construct(
        EntityManagerInterface $em,
        LeadService $leadService,
        ChatService $chatService,
        ClientService $clientService,
        AuthService $authService,
        ErrorHandlerService $errorHandlerService,
        ClientRepository $clientRepository
    ) {
        $this->leadService = $leadService;
        $this->chatService = $chatService;
        $this->clientService = $clientService;
        $this->authService = $authService;
        $this->errorHandlerService = $errorHandlerService;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("/create-lead", methods={"POST"})
     */
    public function createLeadAction(Request $request): JsonResponse
    {
        $input = $this->requestInput($request);
        $service = $this->leadService;
        $user = $this->em->getRepository(User::class)->find($input->user);

        if ($user === null) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $service->addLead(
                $input->name, $input->email, $user, $input->phone
            );

            $client = $this
                ->clientService
                ->addClient($input->name, $input->email, $user, $input->phone);

            $client->setPassword($input->password);
            $this->em->flush();

            return new JsonResponse($this->authService->getClientData($client));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Something went wrong'], 422);
        }
    }

    /**
     * @Route("/feedback", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $em = $this->em;
        $client = $this->requestClientByToken($request);
        $input = $this->requestInput($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $client->getUser();

        if ($user->getUserSettings() === null) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
        }

        $questions = ['period', 'motivation', 'sleep', 'energy', 'plans', 'cravings', 'workouts'];
        if ($client->isMale()) {
            //if male, don't include period.
            unset($questions[0]);
        }

        $mainQuestions = collect($questions)->flip();
        $trainerQuestions = rescue(function () use ($user) {
            $checkInQuestions = $user->getUserSettings()->getCheckInQuestions();
            $content = json_decode($checkInQuestions, true); /** @phpstan-ignore-line */

            return collect($content['questions'] ?? [])
                ->values()
                ->keyBy(function ($question, $index) {
                    $type = $question['type'] ?? 'slider';
                    return $question['name'] ?? sprintf('input_%s_%d', $type, $index);
                });
        }, collect());

        $inputs = $mainQuestions
            ->merge($trainerQuestions)
            ->filter(function ($_, $key) use ($input) {
                return isset($input->$key);
            })
            ->map(function ($question, $key) use ($input, $mainQuestions) {
                $isPredefined = $mainQuestions->has($key);
                $value = isset($input->$key) ? $input->$key : null;
                $type = 'slider';
                $readable = null;
                $class = null;

                $readable = trim($value);

                if ($readable) {
                    return [
                        'label' => $question['label'] ?? null,
                        'readable' => $readable,
                        'value' => $value,
                        'custom' => !$isPredefined,
                        'type' => $question['type'] ?? null
                    ];
                }

                return null;
            })
            ->filter(function ($input) {
                return $input;
            });

        $sliders = $inputs
            ->where('custom', false)
            ->map(function ($input) {
                return $input['value'];
            });

        $answers = $inputs
            ->where('custom', true)
            ->keyBy(function ($input) {
                return $input['label'];
            })
            ->map(function ($input) {
                //check of input type on order to store valid data type value, int|string
                return $input['type'] !== 'slider' ? $input['readable'] : $input['value'];
            });

        $content = collect();

        if ($sliders->isNotEmpty()) {
            $content->put('sliders', $sliders);
        }

        if ($answers->isNotEmpty()) {
            $content->put('answers', $answers);
        }

        if (isset($input->message) && $input->message) {
            $content->put('message', $input->message);
        }

        $parameters = $inputs
            ->keyBy(function ($input, $key) {
                return $input['custom'] ? $input['label'] : $key;
            })
            ->map(function ($input) {
                if ($input['custom']) {
                    return $input['readable'] ?: $input['value'];
                }
                return $input['readable'];
            });

        try {
            $progressFeedback = (new ProgressFeedback($client, json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)));

            $em->persist($progressFeedback);
            $em->flush();

            //check if user has enabled posting the check-in to the chat.
            if ($user->getUserSettings()->getPostCheckinsToChat()) {
                if ($content->has('message')) {
                    $parameters->put('message', nl2br($content->get('message')));
                }

                $msg = $parameters->map(function ($item, $key) use ($user) {
                    $value = is_numeric($item) && $user->getUserSettings()->getCheckInQuestions() !== null ? "{$item}/5" : $item;
                    return sprintf('<strong>%s:</strong> %s', ucwords($key), $value);
                })->implode('<br />');

                $this
                    ->chatService
                    ->sendMessage($msg, $client, $client->getUser());
            }

        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
        }

        return new JsonResponse();
    }
}
