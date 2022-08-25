<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Consumer\PdfGenerationEvent;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Services\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Helper\MealHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ReactApiBundle\Controller\Controller as sfController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Repository\ClientRepository;

/**
 * @Route("/meals")
 */
class MealsController extends sfController
{
    private MealHelper $mealHelper;
    private MessageBusInterface $messageBus;
    private MasterMealPlanRepository $masterMealPlanRepository;

    public function __construct(
        MealHelper $mealHelper,
        MessageBusInterface $messageBus,
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        MasterMealPlanRepository $masterMealPlanRepository
    ) {
        $this->mealHelper = $mealHelper;
        $this->messageBus = $messageBus;
        $this->masterMealPlanRepository = $masterMealPlanRepository;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);
        $mealHelper = $this->mealHelper;

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $plans = $this
            ->masterMealPlanRepository
            ->getByClient($client, [
                MasterMealPlan::STATUS_ACTIVE,
                MasterMealPlan::STATUS_INACTIVE
            ]);

        return new JsonResponse($mealHelper->serializePlans($plans));
    }

    /**
     * @Route("/save-pdf", methods={"POST"})
     */
    public function saveMealPDFAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);
        $input = $this->requestInput($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $email = trim($client->getEmail());
        if ($email === '') {
            throw new BadRequestHttpException('Client does not have email address');
        }
        $name = trim($client->getName());

        $this->messageBus->dispatch(
            new PdfGenerationEvent(
                PdfService::TYPE_MEAL,
                (int) $input->plan,
                $name,
                $email,
                PdfService::V2
            )
        );

        return new JsonResponse(['success' => true]);
    }
}
