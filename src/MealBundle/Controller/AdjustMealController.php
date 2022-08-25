<?php

namespace MealBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\User;
use AppBundle\Services\MealPlanService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/meal/adjust")
 */
class AdjustMealController extends Controller
{
    private MealPlanService $mealPlanService;

    public function __construct(
        MealPlanService $mealPlanService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->mealPlanService = $mealPlanService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/plans/generate/{client}", name="generate_meal_plan")
     * @Route("/plans/generate/template", name="generate_meal_template")
     * @param Client $client
     * @param Request $request
     * @return Response
     */
    public function generateMealPlanAction(Request $request, ?Client $client = null)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if ($client !== null && !$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $numberOfMealPlans = $request->request->get('plans') == "" ? false : $request->request->get('plans');
        $numberOfMeals = $request->request->get('meals') == "" ? false : $request->request->get('meals');
        $desiredKcals = $request->request->getInt('kcal');
        $macroSplit = $request->request->get('macro_split');
        $lang = $request->request->get('language');
        $name = $request->request->get('name') == "" ? false : $request->request->get('name');
        $foodPreferences = [];
        $ingredientsToExclude = [];

        $plans = [];

        if (!$name) {
            return new JsonResponse([
                'reason' => 'You haven\'t named your plan(s).'
            ], 422);
        }

        if (!$numberOfMealPlans) {
            return new JsonResponse([
                'reason' => 'You\'re missing number of meal plans.'
            ], 422);
        }

        if (!$numberOfMeals) {
            return new JsonResponse([
                'reason' => 'You\'re missing number of meals.'
            ], 422);
        }

        if (!$desiredKcals) {
            return new JsonResponse([
                'reason' => 'You\'re missing desired kcal amount.'
            ], 422);
        }

        $meals = $this->getMeals($numberOfMeals);

        $service = $this->mealPlanService;

        for ($i = 1; $i <= $numberOfMealPlans; $i++) {
            $plan = new MasterMealPlan($user);
            $plan
                ->setName("$name #$i")
                ->setLastUpdated(new \DateTime())
                ->setActive(true)
                ->setTemplate(!$client)
                ->setClient($client)
                ->setDesiredKcals($desiredKcals)
                ->setLocale($lang)
                ->setMacroSplit($macroSplit);

            $em = $this->getEm();
            $em->persist($plan);
            $em->flush();

            /** @phpstan-ignore-next-line */
            $service->generateMealPlanFromKcalAndPreferences($plan, $foodPreferences, $meals, $ingredientsToExclude);

            if ($client) {
                $client->setMealUpdated(new \DateTime('now'));
                $em->flush();
            }

            $plans[] = $plan->getId();

        }

        $res = [
            'plans' => $plans,
            'client' => $client ? $client->getFirstName() : null
        ];

        return new JsonResponse($res, 200);
    }

    /**
     * @Route("/plans/add/intro/{client}", name="meal_plan_add_intro")
     * @Route("/plans/add/intro/template", name="meal_template_add_intro")
     * @param Client $client
     * @param Request $request
     * @return Response
     */
    public function addIntroToMealPlanAction(Request $request, ?Client $client = null)
    {
        $plans = $request->request->get('plans');
        $intro = $request->request->get('intro');
        $intro = strip_tags(str_replace('</p>', ' ', $intro));
        $em = $this->getEm();

        foreach (json_decode($plans, true, 512, JSON_THROW_ON_ERROR) as $plan) {
            /** @var ?MasterMealPlan $masterMealPlan */
            $masterMealPlan = $em->getRepository(MasterMealPlan::class)->find($plan);
            if ($masterMealPlan === null) {
                throw new NotFoundHttpException();
            }
            $mealPlan = $masterMealPlan->getMealPlansWhereParentIsNull()[0];
            $mealPlan->setComment($intro);
        }

        $em->flush();

        return new JsonResponse('OK', 200);
    }

    /**
     * @Route("/plans/replace/{mealPlan}", name="replace_meal_plan")
     * @Method("POST")
     * @param MealPlan $mealPlan
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function replaceRecipeAction(MealPlan $mealPlan, Request $request)
    {
        $mealPlanParent = $mealPlan->getParent();
        $plan = $mealPlanParent ? $mealPlanParent->getMasterMealPlan() : $mealPlan->getMasterMealPlan();
        $mealId = $request->request->get('mealId');

        $service = $this->mealPlanService;
        /** @phpstan-ignore-next-line */
        $newMeal = $service->replaceRecipe($plan, $mealPlan, [1111], $mealId);
        return new JsonResponse($newMeal);
    }

    /**
     * @Route("/plans/remove/{mealPlan}", name="remove_meal_plan")
     * @Method("POST")
     * @param MealPlan $mealPlan
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeRecipeAction(MealPlan $mealPlan)
    {
        $em = $this->getEm();
        $plan = $mealPlan->getMasterMealPlan();
        $em->remove($mealPlan);
        $em->flush();
        $res = ['plan' => $plan->getId()];
        return new JsonResponse($res, 200);
    }

    /**
     * @Route("/plans/add/{plan}", name="add_meal_plan")
     * @Method("POST")
     * @param Request $request
     * @param MasterMealPlan $plan
     *
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addMealPlanAction(MasterMealPlan $plan, Request $request)
    {
        $type = $request->request->get('type');
        $recipeId = $request->request->get('mealId');

        $service = $this->mealPlanService;
        /** @phpstan-ignore-next-line */
        $newMeal = $service->addRecipe($plan, $type, [], $recipeId);

        return new JsonResponse($newMeal);
    }

    /**
     * @Route("/plans/recalculate/kcals/{plan}", name="recalculate_kcals")
     * @param MasterMealPlan $plan
     *
     * @return Response
     */
    public function recalculateKcalsAction(MasterMealPlan $plan)
    {
        $service = $this->mealPlanService;
        $service->updatePlanHelper($plan, $plan->getDesiredKcals());
        $kcalsDiff = abs($plan->getKcals() - $plan->getDesiredKcals());

        $res = ['total_kcals' => $plan->getKcals(), 'kcals_diff' => $kcalsDiff, 'plan' => $plan->getId()];
        return new JsonResponse($res, 200);
    }

    /**
     * @Route("/meal-orders/{plan}", name="update_meal_orders")
     * @Method("POST")
     * @param MasterMealPlan $plan
     * @param Request $request
     *
     * @return Response
     */
    public function mealOrdersAction(MasterMealPlan $plan, Request $request)
    {
        $orders = (array) $request->request->get('orders');

        if (!$plan) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        if (count($orders) === 0) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_MODIFIED);
        }

        $affected = 0;

        foreach ($plan->getMealPlans() as $mealPlan) {
            $id = $mealPlan->getId();

            if (isset($orders[$id])) {
                $mealPlan->setOrder((int)$orders[$id]);
                $affected += 1;
            }
        }

        try {
            if ($affected) {
                $this->getEm()->flush();
                return new JsonResponse([], 200);
            }

            return new JsonResponse([], JsonResponse::HTTP_NOT_MODIFIED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/plans/accept/kcals/{plan}", name="accept_kcals")
     * @param MasterMealPlan $plan
     *
     * @return JsonResponse
     */
    public function acceptKcalsAction(MasterMealPlan $plan)
    {
        $em = $this->getEm();
        $plan->setApproved(true);
        $em->flush();
        return new JsonResponse('OK');
    }

    private function getMeals($numberOfMeals): array
    {
        switch ($numberOfMeals) {
            case 3:
                $meals = [MealPlan::TYPE_BREAKFAST, MealPlan::TYPE_LUNCH, MealPlan::TYPE_DINNER];
                break;
            case 4:
                $meals = [MealPlan::TYPE_BREAKFAST, MealPlan::TYPE_LUNCH, MealPlan::TYPE_DINNER, MealPlan::TYPE_EVENING_SNACK];
                break;
            case 5:
                $meals = [MealPlan::TYPE_BREAKFAST, MealPlan::TYPE_MORNING_SNACK, MealPlan::TYPE_LUNCH, MealPlan::TYPE_AFTERNOON_SNACK, MealPlan::TYPE_DINNER];
                break;
            case 6:
                $meals = [MealPlan::TYPE_BREAKFAST, MealPlan::TYPE_MORNING_SNACK, MealPlan::TYPE_LUNCH, MealPlan::TYPE_AFTERNOON_SNACK, MealPlan::TYPE_DINNER, MealPlan::TYPE_EVENING_SNACK];
                break;
            case 7:
                $meals = [MealPlan::TYPE_BREAKFAST, MealPlan::TYPE_MORNING_SNACK, MealPlan::TYPE_LUNCH, MealPlan::TYPE_AFTERNOON_SNACK, MealPlan::TYPE_DINNER, MealPlan::TYPE_EVENING_SNACK, MealPlan::TYPE_EVENING_SNACK];
                break;
            default:
                throw new \RuntimeException('Unsupported amount of meals');
        }

        $data = [];
        $i = 0;
        $percentage = array_values(MealPlan::MEAL_PERCENTAGE_SPLIT[$numberOfMeals]);
        foreach($meals as $type) {
          $data[] = [
            'type' => $type,
            'percentage' => $percentage[$i]
          ];

          $i++;
        }

        return $data;
    }


}
