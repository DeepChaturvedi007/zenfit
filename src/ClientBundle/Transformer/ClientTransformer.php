<?php

namespace ClientBundle\Transformer;

use AppBundle\Entity\Client;
use AppBundle\Repository\QuestionRepository;
use ChatBundle\Repository\MessageRepository;
use League\Fractal\TransformerAbstract;
use AppBundle\Repository\ClientStatusRepository;
use AppBundle\Repository\PaymentRepository;
use AppBundle\Repository\PaymentsLogRepository;
use AppBundle\Repository\ClientReminderRepository;
use AppBundle\Repository\BodyProgressRepository;
use AppBundle\Repository\QueueRepository;
use AppBundle\Repository\ClientImageRepository;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Repository\DocumentClientRepository;
use AppBundle\Repository\VideoClientRepository;
use ProgressBundle\Services\ClientProgressService;

class ClientTransformer extends TransformerAbstract
{
    private MessageRepository $messageRepository;
    private ClientStatusRepository $clientStatusRepository;
    private PaymentRepository $paymentRepository;
    private PaymentsLogRepository $paymentsLogRepository;
    private ClientReminderRepository $clientReminderRepository;
    private BodyProgressRepository $bodyProgressRepository;
    private QueueRepository $queueRepository;
    private ClientImageRepository $clientImageRepository;
    private MasterMealPlanRepository $masterMealPlanRepository;
    private WorkoutPlanRepository $workoutPlanRepository;
    private ClientProgressService $clientProgressService;
    private DocumentClientRepository $documentClientRepository;
    private VideoClientRepository $videoClientRepository;
    private QuestionRepository $questionRepository;

    public function __construct(
        MessageRepository $messageRepository,
        QuestionRepository $questionRepository,
        ClientStatusRepository $clientStatusRepository,
        PaymentRepository $paymentRepository,
        PaymentsLogRepository $paymentsLogRepository,
        ClientReminderRepository $clientReminderRepository,
        BodyProgressRepository $bodyProgressRepository,
        QueueRepository $queueRepository,
        ClientImageRepository $clientImageRepository,
        MasterMealPlanRepository $masterMealPlanRepository,
        WorkoutPlanRepository $workoutPlanRepository,
        ClientProgressService $clientProgressService,
        DocumentClientRepository $documentClientRepository,
        VideoClientRepository $videoClientRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->clientStatusRepository = $clientStatusRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentsLogRepository = $paymentsLogRepository;
        $this->clientReminderRepository = $clientReminderRepository;
        $this->bodyProgressRepository = $bodyProgressRepository;
        $this->queueRepository = $queueRepository;
        $this->questionRepository = $questionRepository;
        $this->clientImageRepository = $clientImageRepository;
        $this->masterMealPlanRepository = $masterMealPlanRepository;
        $this->workoutPlanRepository = $workoutPlanRepository;
        $this->clientProgressService = $clientProgressService;
        $this->documentClientRepository = $documentClientRepository;
        $this->videoClientRepository = $videoClientRepository;
    }

    /** @return array<string, mixed> */
    public function transform(Client $client): array
    {
        $messages = $this
            ->messageRepository
            ->getMessageStatsByClient($client);

        $status = $this
            ->clientStatusRepository
            ->getStatusByClient($client);

        $payments = $this
            ->paymentRepository
            ->getAllPaymentsByClient($client);

        $paymentsLog = $this
            ->paymentsLogRepository
            ->findByClient($client);

        $reminders = $this
            ->clientReminderRepository
            ->findByClient($client);

        $queue = $this
            ->queueRepository
            ->findLatestInvitationByClient($client);

        $images = $this
            ->clientImageRepository
            ->findByClient($client);

        $workoutPlans = $this
            ->workoutPlanRepository
            ->getAllByClientAndUser($client, $client->getUser());

        $docs = $this
            ->documentClientRepository
            ->findByClient($client);

        $videos = $this
            ->videoClientRepository
            ->findByClient($client);

        $checkIns = $this
            ->bodyProgressRepository
            ->getProgressByClient($client);

        $clientProgress = $this
            ->clientProgressService
            ->setClient($client)
            ->setProgressValues()
            ->setUnits()
            ->getProgress();

        $progressMetrics = collect($clientProgress)
            ->only(['direction', 'left', 'progress', 'percentage', 'weekly', 'last', 'start', 'goal', 'weekly', 'lastWeek', 'offText', 'progressText', 'unit'])
            ->all();

        //end getting data from repo

        $progress = [
            'checkIns' => $checkIns,
            'metrics' => $progressMetrics
        ];

        $queue = $queue ? [
            'id' => $queue->getId(),
            'payment' => $queue->getPayment() ? true : false,
            'datakey' => $queue->getDatakey(),
            'createdAt' => $queue->getCreatedAt()
        ] : null;

        $userStripe = $client->getUser()->getUserStripe() !== null ? [
            'feePercentage' => $client->getUser()->getUserStripe()->getFeePercentage()
        ] : null;

        try {
            $gymAdmin = $client->getUser()->getGymAdmin();
        } catch (\Throwable $e) {
            $gymAdmin = $client->getUser();
        }

        $customQuestions = $this->questionRepository->createQueryBuilder('q')
            ->select('q.id, q.text, q.subtitle, q.type, q.inputType, q.options, q.placeholder, q.defaultValue, a.answer')
            ->andWhere('q.user = :user')
            ->setParameter('user', $gymAdmin)
            ->leftJoin('q.answers', 'a', 'WITH', 'a.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();

        return [
            'id' => $client->getId(), //also available in 'info' array (deprecate soon)
            'name' => $client->getName(), //also available in 'info' array (deprecate soon)
            'firstName' => $client->getFirstName(),
            'info' => $client->getClientInfo(),
            'customQuestions' => $customQuestions,
            'email' => $client->getEmail(), //also available in 'info' array (deprecate soon)
            'photo' => $client->getPhoto(), //also available in 'info' array (deprecate soon)
            'active' => $client->getActive(),
            'demoClient' => $client->getDemoClient(),
            'createdAt' => $client->getCreatedAt(),
            'startDate' => $client->getStartDate(),
            'duration' => $client->getDuration(),
            'endDate' => $client->getEndDate(),
            'dayTrackProgress' => $client->getDayTrackProgress(),
            'tags' => $client->tagsList(),
            'clientStripe' => $client->getClientStripe(),
            'status' => $status,
            'images' => $images,
            'reminders' => $reminders,
            'payments' => $payments,
            'paymentsLog' => $paymentsLog,
            'progress' => $progress,
            'docs' => $docs,
            'videos' => $videos,
            'measuringSystem' => $client->getMeasuringSystem(),
            'answeredQuestionnaire' => $client->getAnsweredQuestionnaire(),
            'goalWeight' => $client->getGoalWeight(),
            'bodyProgressUpdated' => $client->getBodyProgressUpdated(),
            'workoutUpdated' => $client->getWorkoutUpdated(),
            'mealUpdated' => $client->getMealUpdated(),
            'updateWorkoutSchedule' => $client->getUpdateWorkoutSchedule(),
            'updateMealSchedule' => $client->getUpdateMealSchedule(),
            'messages' => $messages,
            'queue' => $queue,
            'workoutPlans' => $workoutPlans,
            'trainer' => [
                'id' => $client->getUser()->getId(),
                'name' => $client->getUser()->getName(),
                'userStripe' => $userStripe
            ]
        ];
    }

    /** @return array<string, mixed> */
    public function transformForList(Client $client): array
    {
        $status = $this
            ->clientStatusRepository
            ->getStatusByClient($client);

        $payments = $this
            ->paymentRepository
            ->getAllPaymentsByClient($client);

        $reminders = $this
            ->clientReminderRepository
            ->findByClient($client);

        $queue = $this
            ->queueRepository
            ->findLatestInvitationByClient($client);

        $queue = $queue ? [
            'id' => $queue->getId(),
            'payment' => $queue->getPayment() ? true : false,
            'datakey' => $queue->getDatakey(),
            'createdAt' => $queue->getCreatedAt()
        ] : null;

        return [
            'id' => $client->getId(),
            'name' => $client->getName(),
            'firstName' => $client->getFirstName(),
            'info' => $client->getClientInfo(),
            'email' => $client->getEmail(),
            'photo' => $client->getPhoto(),
            'active' => $client->getActive(),
            'createdAt' => $client->getCreatedAt(),
            'startDate' => $client->getStartDate(),
            'duration' => $client->getDuration(),
            'endDate' => $client->getEndDate(),
            'tags' => $client->tagsList(),
            'status' => $status,
            'reminders' => $reminders,
            'payments' => $payments,
            'measuringSystem' => $client->getMeasuringSystem(),
            'goalWeight' => $client->getGoalWeight(),
            'bodyProgressUpdated' => $client->getBodyProgressUpdated(),
            'queue' => $queue,
            'demoClient' => $client->getDemoClient(),
            'trainer' => [
                'id' => $client->getUser()->getId(),
                'name' => $client->getUser()->getName(),
            ]
        ];
    }
}
