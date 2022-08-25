<?php

namespace MealBundle\Controller;

use AppBundle\Entity\Recipe;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Repository\MealPlanRepository;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\MealPlanService;
use AppBundle\Services\RecipesService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Controller\Controller;
use AppBundle\Entity\MasterMealPlan;
use MealBundle\Helper\MealHelper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/meal")
 */
class ApiController extends Controller
{
    public function __construct(
        private MealPlanService $mealPlanService,
        private MealHelper $mealHelper,
        private RecipesService $recipesService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private MasterMealPlanRepository $masterMealPlanRepository,
        private MealPlanRepository $mealPlanRepository,
        private CurrentUserFetcher $currentUserFetcher,
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/plans/{id}")
     * @Method({"GET"})
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return Response
     */
    public function plansAction(MasterMealPlan $plan, Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if ($plan->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }

        $client = $plan->getClient();
        if ($client !== null && !$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $mealHelper = $this->mealHelper;

        $meal = $request->query->get('meal') != '' ? $request->query->get('meal') : null;
        if ($meal) {
          $meal = $this
              ->mealPlanRepository
              ->find($meal);

          if ($meal === null) {
              throw new NotFoundHttpException();
          }

          $mealHelper->setMeal($meal);
        }

        $plans = $mealHelper->serializeMealPlans($plan);
        return new JsonResponse($mealHelper->transformParentMealPlans($plans));
    }

    /**
     * @Route("/save/{plan}")
     * @Method({"POST"})
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return JsonResponse
     */
    public function savePlan(MasterMealPlan $plan, Request $request)
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $planClient = $plan->getClient();

        if ($plan->getUser()->getId() !== $user->getId() || ($planClient !== null && !$this->clientBelongsToUser($planClient))) {
            throw new AccessDeniedHttpException();
        }

        $content = $request->getContent();
        $locale = $request->cookies->get('meal_products_locale', 'en');

        $array = [];
        $post = null;

        if (!empty($content)) {
            $post = json_decode($content, true);

            if (isset($post['data']['locale'])) {
                $plan->setLocale($post['data']['locale']);
            }

            if (isset($post['data']['results']) && is_array($post['data']['results'])) {
                $array = $post['data']['results'];
            }
        }

        $service = $this->mealPlanService;

        try {
            $data = $service->syncPlan($plan, $array, $locale);

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @Route("/recipes/{id}")
     * @Method({"GET"})
     * @param Recipe $recipe
     * @return Response
     */
    public function recipesAction(Recipe $recipe)
    {
        $data = $this
            ->recipesService
            ->serializeRecipe($recipe);

        return new JsonResponse($data);
    }

    /**
     * @Route("/recipes/{id}/sync")
     * @Method({"POST"})
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    public function recipesSyncAction(Recipe $recipe, Request $request)
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $recipesService = $this->recipesService;
        $content = $request->getContent();
        $params = new ParameterBag();

        if (!empty($content)) {
            $post = json_decode($content, true);
            $params->add($post['data']);
        }

        $reason = null;
        $status = 200;

        try {
            $recipesService->update($recipe, $params, $user, false);
            $recipe = $recipesService->syncProducts($recipe, $params, $user);

            $data = $this
                ->recipesService
                ->serializeRecipe($recipe);

            return new JsonResponse($data, $status);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = 403;
        } catch (\Exception $e) {
            $reason = 'Unable to update recipe.';
            $status = 500;
        }

        return new JsonResponse(compact('reason'), $status);
    }

    /**
     * @Route("/client/kcals/{client}", methods={"GET"})
     */
    public function getClientKcals(Client $client, Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $kcals = $this
            ->masterMealPlanRepository
            ->getPreviousMealPlanKcalsByClient($client);

        return new JsonResponse($kcals);
    }
}
