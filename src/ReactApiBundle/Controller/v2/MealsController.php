<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Consumer\PdfGenerationEvent;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Services\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Helper\MealHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ReactApiBundle\Controller\Controller as sfController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Repository\ClientRepository;

/**
 * @Route("/v2/meals")
 */
class MealsController extends sfController
{
    private MealHelper $mealHelper;
    private MessageBusInterface $messageBus;

    public function __construct(
        MealHelper $mealHelper,
        MessageBusInterface $messageBus,
        EntityManagerInterface $em,
        ClientRepository $clientRepository
    ) {
        $this->mealHelper = $mealHelper;
        $this->messageBus = $messageBus;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function getAction(Request $request)
    {
        $client = $this->requestClient($request);
        $mealHelper = $this->mealHelper;

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $plans = $this
            ->em
            ->getRepository(MasterMealPlan::class)
            ->getByClient($client, [
                MasterMealPlan::STATUS_ACTIVE,
                MasterMealPlan::STATUS_INACTIVE
            ]);

        //check if client has been activated
        //and created after 5th of Jan
        $date = new \DateTime('2021-01-05');
        if (!$client->hasBeenActivated() && $client->getCreatedAt() > $date) {
            $plans = [];
        }

        return new JsonResponse($mealHelper->serializePlans($plans));
    }

    /**
     * @Route("/save-pdf", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveMealPDFAction(Request $request)
    {
        $client = $this->requestClient($request);
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

    /**
     * @Route("/subscribe", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function subscribeAction(Request $request)
    {
        try {
          $em = $this->em;
          $input = $this->requestInput($request);
          $repo = $em->getRepository(MasterMealPlan::class);

          $plan = $repo->find($input->planId);
          if ($plan === null) {
              throw new NotFoundHttpException('Plan not found');
          }
          $client = $plan->getClient();
          if ($client === null) {
              throw new \RuntimeException('Plan has no client attached');
          }
          $subscribedPlan = $repo->getSubscribedMasterMealPlanByClient($client);

          if($subscribedPlan && $subscribedPlan->getId() == $plan->getId()) {
            //if user is unsubscribing a plan
            $subscribedPlan->setStarted(null);
          } elseif($subscribedPlan && $subscribedPlan->getId() != $plan->getId()) {
            //if user is subscribing to a new plan and replacing old
            $subscribedPlan->setStarted(null);
            $plan->setStarted(new \DateTime('now'));
          } else {
            //user is subscribing to a new plan
            $plan->setStarted(new \DateTime('now'));
          }

          $em->flush();

          return new JsonResponse(['success' => true]);
        } catch (\Throwable $e) {
          return new JsonResponse([
            'message' => 'Something went wrong!'
          ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
