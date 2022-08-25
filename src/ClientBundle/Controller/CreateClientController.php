<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Repository\QueueRepository;
use AppBundle\Repository\ClientImageRepository;

#[Route("/client")]
class CreateClientController extends Controller
{
    private QueueRepository $queueRepository;
    private ClientImageRepository $clientImageRepository;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        QueueRepository $queueRepository,
        ClientImageRepository $clientImageRepository
    ) {
        $this->queueRepository = $queueRepository;
        $this->clientImageRepository = $clientImageRepository;
        parent::__construct($em, $tokenStorage);
    }

    #[Route("/clientActivation", name: "clientActivation", methods: ["GET"])]
    public function activateAction(Request $request): Response
    {
        $datakey = $request->query->get('datakey');
        $onlySurvey = $request->query->get('only_survey');

        if ($datakey === null) {
            return new Response("Please provide key");
        }

        $queue = $this
            ->queueRepository
            ->findOneByDatakey($datakey);

        if ($queue === null) {
            return new Response("Invalid key");
        }

        if ($onlySurvey !== null) {
            $config = 'survey';
        } elseif ($queue->getSurvey()) {
            $config = 'full';
        } else {
            $config = 'activation';
        }

        $client = $queue->getClient();
        $customQuestions = $client->getUser()->getQuestions(false);
        $answers = $client->getAnswers(true);
        $locale = $client->getLocale();
        $clientImages = $this
            ->clientImageRepository
            ->findByClient($client, 3, 0, 'ASC');

        return $this->render('@Client/Default/signUpFlowClient.html.twig', compact('client', 'customQuestions', 'clientImages', 'queue', 'config', 'answers', 'locale', 'datakey'));
    }

    #[Route("/clientSurvey", name: "clientSurvey", methods: ["GET"])]
    public function surveyAction(Request $request): Response
    {
        return $this->redirectToRoute('clientActivation', [
            'datakey' => $request->query->get('datakey'),
            'only_survey' => $request->query->get('only_survey')
        ]);
    }
}
