<?php

namespace MealBundle\Controller;

use AppBundle\Entity\ClientFoodPreference;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\User;
use AppBundle\Services\ClientService;
use AppBundle\Services\MealPlanService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\Controller;
use AppBundle\Entity\MasterMealPlan;
use Mobile_Detect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/meal")
 */
class DefaultController extends Controller
{
    private MealPlanService $mealPlanService;
    private ClientService $clientService;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        MealPlanService $mealPlanService,
        ClientService $clientService,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->mealPlanService = $mealPlanService;
        $this->clientService = $clientService;
        $this->authorizationChecker = $authorizationChecker;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/clients/{client}", name="meal_client")
     * @Route("/templates", name="meal_templates")
     * @param Client $client
     * @param Request $request
     * @return Response
     */
    public function overviewAction(Request $request, ?Client $client = null)
    {
        $em = $this->getEm();
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $repo = $em->getRepository(MasterMealPlan::class);
        $templates = $repo->getAllByUser($user, true);
        $plans = $client ? $repo->getAllByClientAndUser($client, $user) : $templates;
        $demoClient = $client ? $client->getDemoClient() : null;
        $service = $this->clientService;

        $calories = '';
        if($client) {
            abort_unless(is_owner($user, $client), 403, 'This client does not belong to you.');

            $calories = $service->getKcalNeed($client);
        }

        $foodPreferences = [];
        /** @var ClientFoodPreference $clientFoodPreferences */
        if($client && $clientFoodPreferences = $client->getClientFoodPreferences()) {

            $excludeIngredients = $clientFoodPreferences->getExcludeIngredients();
            if(!empty($excludeIngredients)) {
                array_push($foodPreferences, "Exclude ingredients: $excludeIngredients");
            }

            $clientFoodPreferences = [
                'avoidLactose' => $clientFoodPreferences->getAvoidLactose(),
                'avoidGluten' => $clientFoodPreferences->getAvoidGluten(),
                'avoidNuts' => $clientFoodPreferences->getAvoidNuts(),
                'avoidEggs' => $clientFoodPreferences->getAvoidEggs(),
                'avoidPig' => $clientFoodPreferences->getAvoidPig(),
                'avoidShellfish' => $clientFoodPreferences->getAvoidShellfish(),
                'avoidFish' => $clientFoodPreferences->getAvoidFish(),
                'isVegetarian' => $clientFoodPreferences->isVegetarian(),
                'isVegan' => $clientFoodPreferences->isVegan(),
                'isPescetarian' => $clientFoodPreferences->isPescetarian()
            ];
            foreach ($clientFoodPreferences as $key => $value) {
                if(!!$value) array_push($foodPreferences, $key);
            }

        }
        $foodPreferences = implode(', ', $foodPreferences);

        $unreadClientMessagesCount = $user->unreadMessagesCount($client);

        return $this->render('@Meal/overview-v2.html.twig',
            compact('plans', 'client', 'templates', 'demoClient', 'calories', 'foodPreferences', 'unreadClientMessagesCount')
        );

    }

    /**
     * @Route("/clients/{client}/plan/{plan}/{meal}", name="meal_client_edit", defaults={"meal" = ""})
     * @Route("/templates/{plan}", name="meal_templates_edit")
     */
    public function editAction(Request $request, MasterMealPlan $plan, ?int $client = null, ?string $meal = null): Response
    {
        $em = $this->getEm();

        if($client) {
            $clientRepo = $em->getRepository(Client::class);
            $client = $clientRepo->find($client);
            if ($client === null) {
                throw new  NotFoundHttpException('Client not found');
            }
            if (!$this->clientBelongsToUser($client)) {
                throw new AccessDeniedHttpException();
            }
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $mobileDetector = new Mobile_Detect;
        $query = $request->query->get('q');
        $isMobile = $mobileDetector->isMobile() && !$mobileDetector->isTablet();
        $view = $isMobile ?
            '@Meal/editor_mobile.html.twig' :
            '@Meal/editor.html.twig';

        $templates = $em
            ->getRepository(MasterMealPlan::class)
            ->getAllByUser($user, true);

        $products = $em
            ->getRepository(MealProduct::class)
            ->findByQuery($query, 25, 0, $user, $plan->getLocale());

        $plans = [];
        $locale = $plan->getLocale();

        if ($isMobile) {
            $plans = $em
                ->getRepository(MealPlan::class)
                ->getByMasterPlan($plan);
        }

        $unreadClientMessagesCount = 0;

        if ($client) {
            $demoClient = $client->getDemoClient();
            $unreadClientMessagesCount = $user->unreadMessagesCount($client);
        } else {
            $demoClient = null;
        }

        return $this->render($view,
            compact(
                'plan',
                'plans',
                'client',
                'templates',
                'products',
                'query',
                'locale',
                'demoClient',
                'meal',
                'unreadClientMessagesCount'
            )
        );
    }

    /**
     * @Route("/from-scratch/{plan}", name="meal_from_scratch")
     * @param MasterMealPlan $plan
     * @return RedirectResponse
     */
    public function fromScratchAction(MasterMealPlan $plan)
    {
        /**
         * @var MealPlanService $service
         */
        $service = $this->mealPlanService;
        $service->createPlan("Meal Plan", $plan);

        if ($client = $plan->getClient()) {
            return $this->redirectToRoute('meal_client_edit', [
                'client' => $client->getId(),
                'plan' => $plan->getId(),
            ]);
        }

        return $this->redirectToRoute('meal_templates_edit', [
            'plan' => $plan->getId(),
        ]);
    }

    /**
     * @Route("/recipes", name="meal_recipes")
     * @Method("GET")
     * @param Request $request
     * @return Response
     */
    public function recipesAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $recipes = $this
            ->getEm()
            ->getRepository(Recipe::class)
            ->getByUser($user);

        return $this->render('@Meal/recipes/index.html.twig',
            compact('recipes')
        );
    }

    /**
     * @Route("/recipes/{recipe}", name="meal_recipes_editor")
     * @Method("GET")
     * @param Recipe $recipe
     * @return Response
     */
    public function recipesEditorAction(Recipe $recipe)
    {
        $admin = $this->authorizationChecker->isGranted('ROLE_ADMIN');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $recipeUser = $recipe->getUser();
        if ($recipeUser && $recipeUser->getId() !== $user->getId()) {
            return $this->redirectToRoute('clients');
        }

        if (!$admin && !$recipeUser) {
            return $this->redirectToRoute('clients');
        }

        return $this->render('@Meal/recipes/editor.html.twig',
            compact('recipe', 'admin')
        );
    }


}
