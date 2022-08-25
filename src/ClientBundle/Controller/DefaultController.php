<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Payment;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\Queue;
use AppBundle\Entity\User;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/client")
 */
class DefaultController extends Controller
{
    private ClientService $clientService;
    private string $appHostname;

    public function __construct(
        ClientService $clientService,
        private CurrentUserFetcher $currentUserFetcher,
        string $appHostname,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->clientService = $clientService;
        $this->appHostname = $appHostname;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/info/{client}", name="clientInfo")
     */
    public function clientInfoAction(Client $client)
    {
        $currentUser = $this->currentUserFetcher->getCurrentUser();

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        $service = $this->clientService;
        $queue = $em->getRepository(Queue::class)->findOneBy([
            'client' => $client
        ]);

        abort_unless(is_owner($user, $client), 403, 'This client does not belong to you.');

        $questionnaireLink = $queue ? $queue->getQuestionnaireSurveyOnlyUrl($this->appHostname) : null;
        $creationLink = $queue ? $queue->getClientCreationLink($this->appHostname) : null;

        $estimateCalorieNeedString = $service->getEstimateCaloriesNeedString($client);
        $calories = 0;
        $isEstimatedCalories = false;
        if (!$estimateCalorieNeedString) {
            $isEstimatedCalories = true;
            $calories = $service->getBmrCalc($client);
            $estimateCalorieNeedString = 'BMR: ' . number_format((float) $service->getBmrCalc($client), 0, '.', ',') . 'kcal';
        } else {
            $estimateCalorieNeedString .= ' in order to calculate estimated calorie need for ' . $client->getName() . '.';
        }

        $clientArr = [
            'id' => $client->getId(),
            'duration' => $client->getDuration(),
            'startDate' => $client->getStartDate(),
            'name' => $client->getName(),
            'payments' => null
        ];

        $clientArr['payments'] = $em
            ->getRepository(Payment::class)
            ->getAllPaymentsByClient($client);

        $workoutUpdated = $client->getWorkoutUpdated();
        $mealUpdated = $client->getMealUpdated();

        $paymentsLog = $em->getRepository(PaymentsLog::class)->findByClient($client);
        $now = new \DateTime();
        $unreadClientMessagesCount = $user->unreadMessagesCount($client);

        $questions = $client->getUser()->getGymAdminOrOwnQuestions();

        return $this->render('@App/default/clientInfo.html.twig', compact(
            'client',
            'questions',
            'clientArr',
            'questionnaireLink',
            'isEstimatedCalories',
            'estimateCalorieNeedString',
            'calories',
            'workoutUpdated',
            'mealUpdated',
            'creationLink',
            'paymentsLog',
            'now',
            'unreadClientMessagesCount'
        ));
    }

}
