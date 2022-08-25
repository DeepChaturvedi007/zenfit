<?php

namespace MealBundle\Controller;

use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealPlanProduct;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\User;
use AppBundle\Repository\MealPlanRepository;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Repository\ClientRepository;
use AppBundle\Services\MealPlanService;
use AppBundle\Services\RecipesService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Helper\MealHelper;
use Illuminate\Support\Arr;
use MealBundle\Services\RecipeGenerationService;
use MealBundle\Services\RecipeCustomGeneratorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/meal")
 */
class UniversalController extends Controller
{
    private MealPlanService $mealPlanService;
    private SessionInterface $session;
    private MealHelper $mealHelper;
    private RecipeGenerationService $recipeGenerationService;
    private RecipeCustomGeneratorService $recipeCustomGeneratorService;
    private RecipesService $recipesService;
    private MealPlanRepository $mealPlanRepository;
    private MasterMealPlanRepository $masterMealPlanRepository;
    private ClientRepository $clientRepository;

    public function __construct(
        SessionInterface $session,
        RecipeGenerationService $recipeGenerationService,
        RecipeCustomGeneratorService $recipeCustomGeneratorService,
        MealPlanRepository $mealPlanRepository,
        MasterMealPlanRepository $masterMealPlanRepository,
        ClientRepository $clientRepository,
        MealHelper $mealHelper,
        RecipesService $recipesService,
        MealPlanService $mealPlanService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->mealPlanService = $mealPlanService;
        $this->recipeGenerationService = $recipeGenerationService;
        $this->recipeCustomGeneratorService = $recipeCustomGeneratorService;
        $this->session = $session;
        $this->mealHelper = $mealHelper;
        $this->recipesService = $recipesService;
        $this->mealPlanRepository = $mealPlanRepository;
        $this->masterMealPlanRepository = $masterMealPlanRepository;
        $this->clientRepository = $clientRepository;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/", name="meal_create", methods={"POST"})
     * @return RedirectResponse|JsonResponse
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $repo = $this->masterMealPlanRepository;
        $name = $request->request->get('name');
        $comment = $request->request->get('comment');
        $newPlan = null;

        if (is_array($templates = $request->request->get('templates'))) {
            $templates = $repo->getByIdsAndUser($templates, $user, true);
        } else {
            $templates = [];
        }

        if ($client = $request->request->get('client')) {
            $client = $this
                ->clientRepository
                ->find($client);
        } else {
            $client = null;
        }

        if ($plan = $request->request->get('plan')) {
            $plan = $repo->getByIdAndUser($plan, $user);
        } else {
            $plan = null;
        }

        $service = $this->mealPlanService;

        if(count($templates) > 0) {
            foreach($templates as $template) {
                $newPlan = $service->createMasterPlan(
                    $template->getName(),
                    $comment,
                    $user,
                    $client,
                    $plan,
                    [$template],
                    $template->getDesiredKcals(),
                    $template->getMacroSplit(),
                    $template->getLocale(),
                    $template->getContainsAlternatives(),
                    [],
                    $template->getType()
                );
            }
        } else {
            $newPlan = $service->createMasterPlan($name, $comment, $user, $client, $plan, []);
        }

        if ($request->isXmlHttpRequest()) {
            if ($client === null) {
                throw new \RuntimeException('No client');
            }

            $plans = $repo->getAllByClientAndUser($client, $client->getUser());
            return new JsonResponse($this->mealHelper->serializePlans($plans));
        }

        if ($plan) {
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->mealEditorRedirect($newPlan);

    }

    /**
     * @Method({"GET","DELETE","POST"})
     * @Route("/{plan}/delete", name="meal_delete")
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function deleteAction(MasterMealPlan $plan, Request $request)
    {
        $service = $this->mealPlanService;
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        try {
            $service->deletePlan($currentUser, $plan);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $plan->getId(),
                ], 200);
            }
        } catch (\Exception $error) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $error->getMessage(),
                ], 422);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @Route("/save-as-template/{plan}", name="meal_create_template")
     * @Method({"POST"})
     */
    public function saveAsTemplateAction(MasterMealPlan $plan, Request $request)
    {
        $name = $request->request->get('name');
        $comment = $request->request->get('comment');
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        $service = $this->mealPlanService;

        try {
            $template = $service->createMasterPlan(
                $name,
                $comment,
                $user,
                null,
                $plan,
                [],
                $plan->getDesiredKcals(),
                $plan->getMacroSplit(),
                $plan->getLocale(),
                $plan->getContainsAlternatives(),
                [],
                $plan->getType()
            );

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $template->getId(),
                    'message' => 'Template "' . $name . '" successfully saved.',
                ], 201);
            }

            return $this->redirectToRoute('meal_templates');
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'Cannot save template "' . $name . '", please try again.',
                ], 422);
            }
        }

        return $this->mealEditorRedirect($plan);
    }

    /**
     * @Route("/{plan}/update", name="meal_update")
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function updateAction(MasterMealPlan $plan, Request $request)
    {
        $service = $this->mealPlanService;

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        try {
            $service->updateMasterPlan($currentUser, $plan, $request);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'comment' => $plan->getExplaination(),
                    'updated_at' => $plan->getLastUpdated(),
                ], 200);
            }
        } catch (\Exception $error) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $error->getMessage(),
                ], 422);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/add-plan/{plan}", name="meal_create_plan")
     * @Method({"POST"})
     * @param MasterMealPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createMealPlanAction(MasterMealPlan $plan, Request $request)
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

        $em = $this->getEm();
        $repo = $this->mealPlanRepository;

        $name = $request->request->get('name');
        $id = $request->request->get('id');
        $parentId = $request->request->get('parent_id');
        $response = [
            'status' => 200,
        ];

        if (empty($name)) {
            $response['message'] = 'Plan name cannot be empty';
            $response['status'] = 422;

            goto response;
        }

        $response['meals'] = [];

        if ($id) {
            $mealPlan = $repo->find($id);

            if ($mealPlan === null) {
                throw new NotFoundHttpException();
            }

            $mealPlan->setName($name);
            $em->flush();
        } else {
            /**
             * @var $parent MealPlan|null
             */
            $parent = $parentId ? $repo->find($parentId) : null;

            $previousPlan = null;
            $totalPlans = count($plan->getMealPlans());

            $mealPlan = new MealPlan($plan);
            $mealPlan
                ->setName($name)
                ->setOrder($totalPlans + 1);

            if ($parent) {
                $mealPlan->setParent($parent);
            } else {
                $response['last'] = true;
            }

            $em->persist($mealPlan);
            $em->flush();

            if ($parent) {
                $response['totals'] = $mealPlan->getTotals();
                $response['products'] = [];
            } else {
                // Create Predefined Meals
                $meals = [];

                for ($i = 0; $i < 4; $i++) {
                    $mealName = 'Meal ' . ($i + 1);

                    $meal = $meals[] = new MealPlan($plan);
                    $meal
                        ->setName($mealName)
                        ->setClient($mealPlan->getClient())
                        ->setParent($mealPlan)
                        ->setOrder(1);

                    $em->persist($meal);
                }

                $em->flush();

                $response['totals'] = $mealPlan->getMealsTotal();
                $response['meals'] = array_map(function (MealPlan $meal) {
                    return [
                        'id' => $meal->getId(),
                        'name' => $meal->getName(),
                        'totals' => $meal->getTotals(),
                        'products' => [],
                    ];
                }, $meals);
            }
        }

        $planParent = $mealPlan->getParent();

        $response['id'] = $mealPlan->getId();
        $response['name'] = $mealPlan->getName();
        $response['parent_id'] =$planParent ? $planParent->getId() : null;
        $response['order'] = $mealPlan->getOrder();

        $now = new \DateTime();

        if ($client = $plan->getClient()) {
            $client->setMealUpdated($now);
        }

        $plan->setLastUpdated($now);

        response:
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse($response, $response['status']);
            }

            return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/clone-plan/{plan}", name="meal_clone_plan", methods={"POST"})
     */
    public function cloneMealPlanAction(MasterMealPlan $plan, Request $request): JsonResponse
    {
        $em = $this->getEm();
        $id = $request->request->get('id');
        $name = $request->request->get('name');

        /**
         * @var ?MealPlan $mealPlan
         */
        $mealPlan = $em
            ->getRepository(MealPlan::class)
            ->find($id);

        if ($mealPlan !== null) {
            $order = $mealPlan->getOrder() + 1;
	          $parentPlan = $mealPlan->getParent();

            if ($parentPlan) {
                $newMealPlan = clone $mealPlan;
                $newMealPlan->setName($name);
                $newMealPlan->setOrder($order);
                $em->persist($newMealPlan);
                $childrens = $parentPlan->getChildren()
                    ->filter(function(MealPlan $item) use ($mealPlan) {
                        return $item->getOrder() > $mealPlan->getOrder();
                    });
                $childrens->map(function(MealPlan $item) use (&$order) {
                    $item->setOrder(++$order);
                });
                $em->flush();
                $products = new ArrayCollection();
                foreach ($mealPlan->getProducts()->toArray() as $product) {
                    /**
                     * @var $product MealPlanProduct
                     */
                    $newProduct = clone $product;
                    $newProduct->setPlan($newMealPlan);

                    $products->add($newProduct);
                    $em->persist($newProduct);
                }

                $newMealPlan->setProducts($products);
                $em->flush();
            } else {
                $newPlan = clone $mealPlan;
                $newPlan->setName($name);
                $newPlan->setOrder($order);
                $clientPlan = $plan->getClient();
                if ($clientPlan) {
                    $clientPlans = $em
                        ->getRepository(MealPlan::class)
                        ->getByClient($clientPlan);

                    foreach ($clientPlans as $clientPlan) {
                        /**
                         * @var $clientPlan MealPlan
                         */
                        if ($clientPlan->getOrder() > $mealPlan->getOrder()) {
                            $clientPlan->setOrder(++$order);
                        }
                    }
                }
                $em->persist($newPlan);
                $em->flush();

                foreach ($mealPlan->getChildren()->toArray() as $meal) {
                    /**
                     * @var $meal MealPlan
                     */
                    $newMealPlan = clone $meal;
                    $newMealPlan->setParent($newPlan);

                    $em->persist($newMealPlan);
                    $em->flush();

                    $products = new ArrayCollection();

                    foreach ($meal->getProducts()->toArray() as $product) {
                        /**
                         * @var $product MealPlanProduct
                         */
                        $newProduct = clone $product;
                        $newProduct->setPlan($newMealPlan);

                        $products->add($newProduct);
                        $em->persist($newProduct);
                    }

                    $newMealPlan->setProducts($products);
                    $em->flush();
                }
            }

            $now = new \DateTime();

            if ($client = $plan->getClient()) {
                $client->setMealUpdated($now);
            }

            $plan->setLastUpdated($now);
        }

        return new JsonResponse('OK');
    }

    /**
     * @Route("/{mealPlan}/upload-image", name="meal_upload_image")
     * @Method("POST")
     * @param MealPlan $mealPlan
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function uploadMealImageAction(MealPlan $mealPlan, Request $request)
    {
        $user = $this->getUser();
        $message = 'Meal plan image successfully uploaded.';
        $reason = null;
        $status = JsonResponse::HTTP_OK;

        try {
            if ($image = $request->files->get('image')) {
                $this
                    ->mealPlanService
                    ->attachMealPlanImage($mealPlan, $image, $user, true);
            } else {
                $status = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;
                $reason = 'Image field is required';
            }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = JsonResponse::HTTP_FORBIDDEN;
        } catch (\Exception $e) {
            $reason = 'Unable to upload meal plan image.';
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($reason ? compact('reason') : compact('message'), $status);
        }

        $flash = $this->session->getFlashBag();

        if ($reason) {
            $flash->add('error', $reason);
        } else {
            $flash->add('success', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/apply-recipe/{plan}/{recipe}", name="meal_apply_recipe")
     * @Route("/apply-recipe/{plan}/{recipe}/{meal}", name="meal_apply_recipe")
     * @Method("GET")
     *
     * @param MasterMealPlan $plan
     * @param Recipe $recipe
     * @param Request $request
     * @param MealPlan $meal
     *
     * @return JsonResponse|RedirectResponse
     */
    public function applyMealRecipeAction(MasterMealPlan $plan, Recipe $recipe, Request $request, MealPlan $meal = null)
    {
        $user = $this->getUser();
        $message = 'Recipe successfully applied.';
        $reason = null;
        $status = JsonResponse::HTTP_OK;

        try {
            abort_unless(is_owner($user, $plan), 403, 'Insufficient permissions to upload image');

            /**
             * @var MealPlan $meal
             */
            $meal = Arr::first($plan->getMealPlans()->toArray(), function (MealPlan $meal) {
                return !$meal->getParent() && !$meal->getDeleted();
            });

            if (!$meal) {
                throw new NotFoundHttpException('Meal not found.');
            }

            $ratio = 1;
            if ($plan->getType() == MasterMealPlan::TYPE_CUSTOM_MACROS) {
                $macros = (array) $plan->getParameterByKey('macros');
                $numberOfMeals = count($plan->getMealPlansWhereParentIsNull());
                $planType = $plan->getType();

                $this
                    ->recipeCustomGeneratorService
                    ->recipeBaseService
                    ->setMacros($macros)
                    ->setNumberOfMeals($numberOfMeals)
                    ->setPlan($plan);

                if ($planType !== null) {
                    $this->recipeCustomGeneratorService->recipeBaseService->setType($planType);
                }

                $result = $this
                    ->recipeCustomGeneratorService
                    ->checkIfMealOrRecipeCanHitMacros($recipe, $meal->getType(), $meal);

                $ratio = $result['ratios'];
            }

            $this
                ->recipeGenerationService
                ->recipeBaseService
                ->setPlan($plan);

            $this
                ->recipeGenerationService
                ->addRecipeToMealPlan((int) $recipe->getId(), null, $meal->getType(), $meal, $ratio);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = JsonResponse::HTTP_FORBIDDEN;
        } catch (\Exception $e) {
            $reason = 'Unable to apply recipe to meal plan.';
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($reason ? compact('reason') : compact('message'), $status);
        }

        $flash = $this->session->getFlashBag();

        if ($reason) {
            $flash->add('error', $reason);
        } else {
            $flash->add('success', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/recipes", name="meal_recipes_create")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function createRecipeAction(Request $request)
    {
        $em = $this->getEm();
        $user = $this->getUser();
        $admin = $request->request->get('admin') == 1 ? true : false;

        try {
            $from = rescue(function () use ($em, $request) {
                return $em
                    ->getRepository(Recipe::class)
                    ->find($request->request->get('recipe'));
            }, null);

            $recipe = $this
                ->recipesService
                ->create($request->request, $admin ? null : $user, $from, false);

            if($admin) {
                $recipe->setApproved(false);
            }

            if ($image = $request->files->get('image')) {
                $this
                    ->recipesService
                    ->attachImage($recipe, $image, $admin ? null : $user, false);
            }

            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $recipe->getId(),
                    'name' => $recipe->getName(),
                ]);
            }

            return $this->redirectToRoute('meal_recipes_editor', [
                'recipe' => $recipe->getId(),
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = 403;
        } catch (\Exception $e) {
            $reason = $e->getMessage();
            $status = 500;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(compact('reason'), $status);
        }

        $flash = $this->session->getFlashBag();
        $flash->add('error', $reason);

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/recipes/{recipe}/update", name="meal_recipes_update")
     * @Method("POST")
     * @param Recipe $recipe
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function editRecipeAction(Recipe $recipe, Request $request)
    {
        $user = $this->getUser();
        $admin = $request->request->get('admin') == 1 ? true : false;
        $message = 'Recipe successfully updated.';
        $reason = null;
        $status = 200;
        try {
            $this
                ->recipesService
                ->update($recipe, $request->request, $user, false);

            if ($image = $request->files->get('image')) {
                $this
                    ->recipesService
                    ->attachImage($recipe, $image, $admin ? null : $user, false);
            }

            $this->getEm()->flush();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = 403;
        } catch (\Exception $e) {
            $reason = 'Unable to update recipe.';
            $status = 500;
        }

        if ($request->isXmlHttpRequest()) {
            $id = $recipe->getId();
            $name = $recipe->getName();
            return new JsonResponse($reason ? compact('reason') : compact('message', 'id', 'name'), $status);
        }

        $flash = $this->session->getFlashBag();

        if ($reason) {
            $flash->add('error', $reason);
        } else {
            $flash->add('success', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Method({"GET","DELETE","POST"})
     * @Route("/recipes/{recipe}/delete", name="meal_recipes_delete")
     * @param Recipe $recipe
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function deleteRecipeAction(Recipe $recipe, Request $request)
    {
        $message = 'Recipe successfully deleted';
        $reason = null;
        $status = 200;

        try {
            $user = $this->getUser();
            if ($user === null) {
                throw new AccessDeniedHttpException('Please login');
            }
            $this
                ->recipesService
                ->remove($recipe, $user);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $reason = $e->getMessage();
            $status = 403;
        } catch (\Exception $e) {
            $reason = 'Unable to delete recipe.';
            $status = 500;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($reason ? compact('reason') : compact('message'), $status);
        }

        $flash = $this->session->getFlashBag();

        if ($reason) {
            $flash->add('error', $reason);
        } else {
            $flash->add('success', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
