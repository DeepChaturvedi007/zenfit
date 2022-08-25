<?php

namespace MealBundle\Controller\v3;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\User;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Repository\MealPlanRepository;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\MealPlanService;
use AppBundle\Services\RecipesService;
use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use MealBundle\Helper\MealHelper;
use MealBundle\Services\RecipeGenerationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api/v3/meal")
 */
class ApiController extends Controller
{
    public function __construct(
        private CurrentUserFetcher $currentUserFetcher,
        private MealPlanService $mealPlanService,
        private EntityManagerInterface $em,
        private MealHelper $mealHelper,
        private RecipeGenerationService $recipeGenerationService,
        private TranslatorInterface $translator,
        private RecipesService $recipesService,
        private ValidationService $validationService,
        private EventDispatcherInterface $eventDispatcher,
        private MealPlanRepository $mealPlanRepository,
        private string $env,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/client/{client}")
     * @Method("GET")
     */
    public function getPlansAction(Client $client, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        $repo = $em->getRepository(MasterMealPlan::class);
        $plans = $repo->getAllByClientAndUser($client, $user);
        return new JsonResponse($this->mealHelper->serializePlans($plans));
    }

    /**
     * @Route("/plans/generate/{client}", name="generate_meal_plan")
     */
    public function generateMealPlanApiAction(Request $request, Client $client): JsonResponse
    {
        $currentUser = $this->currentUserFetcher->getCurrentUser();

        if ($client !== null && !$this->clientBelongsToUser($client, $currentUser)) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser;
        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        try {
            $body = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
            $plan = $this
                ->recipeGenerationService
                ->preparePlan((array) $body, $user, $client);

            $this
                ->recipeGenerationService
                ->generatePlan();

            if ($body->type === MasterMealPlan::TYPE_FIXED_SPLIT) {
                $this
                    ->recipeGenerationService
                    ->syncPlan();
            }

            return new JsonResponse([
                'plan' => $plan->getId()
            ]);

        } catch (\Exception $e) {
            if (isset($plan)) {
                $plan->setDeleted(true);
                $this->em->flush();
            }

            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @Route("")
     * @Method("DELETE")
     */
    public function deleteMealAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        /** @var ?MealPlan $meal */
        $meal = $em->getRepository(MealPlan::class)->find($request->query->get('id'));
        if ($meal === null) {
            throw new NotFoundHttpException('Meal plan not found');
        }
        /** @var ?Client $client */
        $client = $em->getRepository(Client::class)->find($request->query->get('client'));
        if ($client === null) {
            throw new NotFoundHttpException();
        }

        if  (!$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $meal->setDeleted(true);
        $em->flush();

        /** @var MasterMealPlanRepository $repo */
        $repo = $em->getRepository(MasterMealPlan::class);
        $plans = $repo->getAllByClientAndUser($client, $user);
        return new JsonResponse($this->mealHelper->serializePlans($plans));
    }

    /**
     * @Route("")
     * @Method("POST")
     */
    public function addMealAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();

        $body = json_decode($request->getContent(), false);

        /** @var ?Client $client */
        $client = $em->getRepository(Client::class)->find($body->client);
        if ($client === null) {
            throw new NotFoundHttpException('Client not found');
        }

        if (!$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $type = isset($body->type) ? $body->type : 0;
        $replaceMeal = isset($body->replaceMeal) ? $em->getRepository(MealPlan::class)->find($body->replaceMeal) : null;

        /** @var MasterMealPlanRepository $repo */
        $repo = $em->getRepository(MasterMealPlan::class);
        $plan = $repo->find($body->plan);
        /** @var ?MealPlan $parent */
        $parent = $em->getRepository(MealPlan::class)->find($body->parent);

        try {
            if ($plan === null) {
                throw new NotFoundHttpException('Plan not found');
            }

            if ($parent === null) {
                throw new NotFoundHttpException('Meal has no parent.');
            }

            $ratio = null;
            $recipeGenerationService = $this
                ->recipeGenerationService
                ->setRecipeBaseProperty('plan', $plan);

            $meal = $recipeGenerationService->addRecipeToMealPlan($body->recipe, $replaceMeal, $type, $parent, $ratio);
            $this->updatePlan($plan, $meal, $parent);
            $recipeGenerationService->syncPlan(false, null, $meal);
        } catch (\Exception $e) {
            return new JsonResponse([
                'err' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'err' => $e->getMessage()
            ], 422);
        }

        $plans = $repo->getAllByClientAndUser($client, $user);
        return new JsonResponse($this->mealHelper->serializePlans($plans));
    }

    /**
     * @Route("")
     * @Method("PUT")
     */
    public function updateMealAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        $body = json_decode($request->getContent(), false);
        /** @var ?MasterMealPlan $plan */
        $plan = $em->getRepository(MasterMealPlan::class)->find($body->plan);
        if ($plan === null) {
            throw new NotFoundHttpException('Plan not found');
        }
        $planClient = $plan->getClient();
        if ($planClient === null || !$this->clientBelongsToUser($planClient)) {
            throw new AccessDeniedHttpException();
        }

        /** @var ?MealPlan $parent */
        $parent = $em->getRepository(MealPlan::class)->find($body->parent);
        if ($parent === null) {
            throw new NotFoundHttpException('Parent plan not found');
        }
        $parent->setMacroSplit($body->macroSplit);

        try {
            $recipeGenerationService = $this
                ->recipeGenerationService
                ->setRecipeBaseProperty('plan', $plan)
                ->setRecipeBaseProperty('foodPreferences', $plan->getParameterByKey('foodPreferences'))
                ->setRecipeBaseProperty('excludeIngredients', $plan->getParameterByKey('excludeIngredients'));

            $recipeGenerationService->regenerateRandomMeals($body->type, $body->macroSplit, $parent);
            $recipeGenerationService->syncPlan(true, $body->type);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'plan' => $plan->getId(),
                'type' => $body->type
            ], 422);
        }

        /** @var MasterMealPlanRepository $repo */
        $repo = $em->getRepository(MasterMealPlan::class);
        $plans = $repo->getAllByClientAndUser($planClient, $user);
        return new JsonResponse($this->mealHelper->serializePlans($plans));
    }

    /**
     * @Route("/save/settings/{plan}", name="save_meal_plan_settings")
     */
    public function saveSettingsAction(MasterMealPlan $plan, Request $request): JsonResponse
    {
        $body = collect(json_decode($request->getContent(), true));

        // check whether we should update plan kcals
        $syncPlan = false;
        $approved = false;

        $em = $this->getEm();
        $fields = ['kcals', 'macros', 'name', 'active', 'message', 'approved', 'duration'];
        $macros = (array) $plan->getParameterByKey('macros');

        foreach ($fields as $field) {
            if (!$body->has($field)) {
                continue;
            }

            $value = $body->get($field);

            if (($field === 'kcals' && $value != $plan->getDesiredKcals()) || ($field === 'macros' && $value != $macros)) {
                $syncPlan = true;
            }

            if ($field === 'approved' && $value === true) {
                $approved = true;
            }

            switch ($field) {
                case 'kcals':
                    $plan->setDesiredKcals($value);
                    break;
                case 'name':
                    if($value && !empty($value)) {
                        $plan->setName($value);
                    }
                    break;
                case 'active':
                    $plan
                        ->setActive((bool) $value)
                        ->setStatus($value ? 'active' : 'hidden');
                    break;
                case 'message':
                    $plan->setExplaination($value);
                    break;
                case 'duration':
                    $this
                        ->mealPlanService
                        ->getMasterMealPlanMeta($plan, $value);
                    break;
                case 'macros':
                    $macros = $value;
                    $parameters = json_decode($plan->getParameters(), true, 512, JSON_THROW_ON_ERROR);
                    break;
            }
        }

        $em->flush();
        $em->refresh($plan);

        if ($syncPlan) {
            $planType = $plan->getType();
            if ($planType !== null) {
                $this->recipeGenerationService->recipeBaseService->setType($planType);
            }

            $this
                ->recipeGenerationService
                ->recipeBaseService
                ->setPlan($plan)
                ->setMacros($macros)
                ->setNumberOfMeals(count($plan->getMealPlansWhereParentIsNull()))
                ->setFoodPreferences($plan->getParameterByKey('foodPreferences'))
                ->setExcludeIngredients($plan->getParameterByKey('excludeIngredients'));

            try {
                $this->recipeGenerationService->updateKcalsInPlan($approved);
            } catch (\Exception $e) {
                return new JsonResponse(['errors' => $e->getMessage()], 422);
            }
        }

        $em->flush();

        return new JsonResponse(Arr::first($this->mealHelper->serializePlans([$plan])));
    }

    /**
     * @Route("/plan/delete")
     * @Method("DELETE")
     */
    public function deletePlanAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();
        /** @var ?MasterMealPlan $plan */
        $plan = $em->getRepository(MasterMealPlan::class)->find($request->query->get('plan'));
        if ($plan === null) {
            throw new NotFoundHttpException();
        }

        $planUser = $plan->getUser();
        if ($planUser->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }
        $planClient = $plan->getClient();
        if ($planClient !== null && !$this->clientBelongsToUser($planClient)) {
            throw new AccessDeniedHttpException();
        }

        $plan->setDeleted(true);
        $em->flush();

        return new JsonResponse('OK');
    }


    /**
     * @Route("/plan/reorder/{plan}")
     * @Method("POST")
     */
    public function reorderPlanAction(MasterMealPlan $plan, Request $request): JsonResponse
    {
        $response = [];

        $content = $request->getContent();
        $data = [];

        if (!empty($content)) {
            $post = json_decode($request->getContent(), true);

            if (isset($post['data']['results']) && is_array($post['data']['results'])) {
                $data = $post['data']['results'];
            }
        }

        if (count($data) === 0) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
        }

        $em = $this->getEm();

        /**
         * @var Collection $mealList
         */
        $mealList = collect($data)
            ->reduce(function (Collection $carry, $item) {
                $item = collect($item);
                return $carry->merge($item->get('meals'))->push($item->except('meals')->toArray());
            }, collect())
            ->filter(function ($item) {
                return $item['id'] > 0;
            })
            ->keyBy('id')
            ->sortBy('parent');

        $removed = collect($plan->getMealPlans())
            ->filter(function (MealPlan $mealPlan) use ($mealList) {
                return $mealPlan->getParent() && !$mealList->has($mealPlan->getId());
            });

        if ($removed->isNotEmpty()) {
            foreach ($removed as $meal) {
                $meal->setDeleted(true);
            }
        }

        $meals = $this->mealPlanRepository
            ->getByIds($mealList->keys()->all());

        foreach ($meals as $meal) {
            $params = collect($mealList->get($meal->getId()));
            $meal->setOrder($params->get('order', $meal->getOrder()));

            if ($parent = $params->get('parent')) {
                $mealParent = $meal->getParent();
                if ($mealParent !== null && $mealParent->getId() != $parent) {
                    //check if meal has been moved from one column to the other
                    //if it has, recalculate kcals for that particular meal
                    $meal
                        ->setParent($em->getReference(MealPlan::class, $parent))
                        ->setType($mealParent->getType());

                    $em->flush();

                    try {
                        $this->updatePlan($plan, $meal);
                    } catch (\Exception $e) {
                        return new JsonResponse([
                            'err' => $e->getMessage()
                        ], 422);
                    }
                }

            }
        }

        $em->flush();

        $response['result'] = Arr::first($this->mealHelper->serializePlans([$plan]));
        return new JsonResponse($response);
    }


    /**
     * @Route("/plan/{plan}/percent-weights")
     * @Method("POST")
     */
    public function postPlanPercentWeightAction(MasterMealPlan $plan, Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = collect([]);

        if (!empty($content)) {
            $post = json_decode($request->getContent(), true);

            if (isset($post['meals']) && is_array($post['meals'])) {
                $data = collect($post['meals'])->pluck('value', 'id');
            }
        }

        if ($data->isEmpty()) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
        }

        $em = $this->getEm();
        $meals = $this->mealPlanRepository
            ->getByIds($data->keys()->all());

        foreach ($meals as $meal) {
            $value = $data->get($meal->getId());
            $meal->setPercentWeight($value > 0 ? (float) $value : null);
            $em->flush();
        }

        try {
            $this->updatePlan($plan);
            $em->flush();
        } catch (\Exception $e) {}

        return new JsonResponse(Arr::first($this->mealHelper->serializePlans([$plan])));
    }

    /**
     * @Route("/plan/{plan}/add-parent")
     * @Method("POST")
     */
    public function addParentToPlanAction(MasterMealPlan $plan, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), false);
        $type = isset($body->type) ? $body->type : $request->request->get('type', null);
        $name = isset($body->name) ? $body->name : $request->request->get('name', 'Extra');

        if(!isset($type)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Meal type is required',
            ], 422);
        }
        $order = $this
            ->getEm()
            ->getRepository(MealPlan::class)
            ->getLastOrderByPlan($plan);

        $title = $type == 0 ? $name : $this->translator->trans('meals.' . $type);

        $meals = $plan->getRootMealPlans();
        $oldMealsCount = count($meals);

        $percentWeight = (float) 0;
        if (isset(MealPlan::MEAL_TYPE_PERCENTAGE[$oldMealsCount+1][$type])) {
            $percentWeight = MealPlan::MEAL_TYPE_PERCENTAGE[$oldMealsCount+1][$type];
        }

        $newParent = $this
            ->recipeGenerationService
            ->recipeBaseService
            ->setPlan($plan)
            ->createParent(
                $title,
                $order + 1,
                $type,
                $percentWeight
            );

        if($percentWeight && $newParent) {
            $shareOfWeight = (float) $newParent->getPercentWeight() / (int) $oldMealsCount;
            $total = (float) $percentWeight;
            /** @var MealPlan $meal */
            foreach ($meals as $meal) {
                $newWeight = round((float) $meal->getPercentWeight() - (float) $shareOfWeight, 2);
                $total = (float) $total + (float) $newWeight;
                $meal->setPercentWeight($newWeight);
                $this->getEm()->persist($meal);
            }
            $difference = (float) 1 - (float) $total;
            $newParent->setPercentWeight( (float) $newParent->getPercentWeight() + (float) $difference);
        }

        $this->getEm()->persist($newParent);

        try {
            $this->updatePlan($plan);
        } catch (\Exception $e) {}

        $this->getEm()->flush();

        $transformed = collect($this->mealHelper->serializePlans([$plan]))->first();
        return new JsonResponse($transformed);
    }

    private function updatePlan(MasterMealPlan $plan, $meal = null, $parent = null): void
    {
        $this
            ->recipeGenerationService
            ->setRecipeBaseProperty('plan', $plan)
            ->setRecipeBaseProperty('type', $plan->getType())
            ->updatePlan($meal, $parent);

        $client = $plan->getClient();
        if ($client !== null) {
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATED_MEAL_PLAN);
            $dispatcher->dispatch($event, Event::TRAINER_UPDATED_MEAL_PLAN);
        }
    }
}
