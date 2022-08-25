<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealPlanProduct;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\MealProductWeight;
use AppBundle\Entity\MasterMealPlanMeta;
use AppBundle\Entity\DefaultMessage;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipeProduct;
use AppBundle\Entity\User;
use AppBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Intervention\Image\ImageManagerStatic as Image;
use MealBundle\Services\RecipeBaseService;;
use Stringy\StaticStringy;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use AppBundle\Repository\DefaultMessageRepository;
use AppBundle\Services\DefaultMessageService;
use function Symfony\Component\Translation\t;

class MealPlanUnauthorized extends \Exception
{
}

class MealPlanService
{
    const TYPE_EDIT = 'edit';
    const TYPE_CLONE = 'clone';
    const TYPE_NEW = 'new';

    private EntityManagerInterface $em;
    private AwsService $aws;
    private EventDispatcherInterface $eventDispatcher;
    private RecipeBaseService $recipeBaseService;
    private DefaultMessageService $defaultMessageService;
    private TokenStorageInterface $tokenStorage;
    private AuthorizationCheckerInterface $authorizationChecker;
    private DefaultMessageRepository $defaultMessageRepository;
    private string $s3beforeAfterImages;
    private string $s3ImagesKeyPrefix;
    private string $s3ImagesBucket;

    public function __construct(
        EntityManagerInterface $em,
        AwsService $aws,
        EventDispatcherInterface $eventDispatcher,
        RecipeBaseService $recipeBaseService,
        DefaultMessageService $defaultMessageService,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        DefaultMessageRepository $defaultMessageRepository,
        string $s3beforeAfterImages,
        string $s3ImagesKeyPrefix,
        string $s3ImagesBucket
    ) {
        $this->em = $em;
        $this->aws = $aws;
        $this->eventDispatcher = $eventDispatcher;
        $this->recipeBaseService = $recipeBaseService;
        $this->defaultMessageService = $defaultMessageService;
        $this->authorizationChecker = $authorizationChecker;
        $this->defaultMessageRepository = $defaultMessageRepository;
        $this->tokenStorage = $tokenStorage;
        $this->s3beforeAfterImages = $s3beforeAfterImages;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->s3ImagesBucket = $s3ImagesBucket;
    }

    /**
     * @param array<mixed> $templates
     * @param array<mixed> $parameters
     */
    public function createMasterPlan(
      string $name,
      ?string $comment,
      User $user,
      ?Client $client = null,
      ?MasterMealPlan $plan = null,
      array $templates = [],
      ?int $desiredKcals = null,
      ?int $macroSplit = null,
      string $locale = 'en',
      bool $containsAlternatives = false,
      array $parameters = [],
      ?int $type = null,
      bool $createEmpty = false
    ) : MasterMealPlan
    {
        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $isTemplate = !$client;
        $newPlan = $plan ? clone $plan : new MasterMealPlan($user);

        $newPlan
            ->setExplaination($comment)
            ->setName($name ? $name : 'plan')
            ->setLastUpdated(new \DateTime())
            ->setActive(true)
            ->setTemplate($isTemplate)
            ->setClient($client)
            ->setUser($user)
            ->setDesiredKcals($desiredKcals)
            ->setMacroSplit($macroSplit)
            ->setLocale($locale)
            ->setContainsAlternatives($containsAlternatives)
            ->setType($type)
            ->setParameters((string) json_encode($parameters, JSON_THROW_ON_ERROR));

        $this->em->persist($newPlan);
        $this->em->flush();

        if ($plan) {
            $templates[] = $plan;
        }

        if(!$createEmpty) {
            if (count($templates) === 0) {
                $this->createPlan($name, $newPlan);
            } else {
                $this->cloneMasterPlans($templates, $newPlan);
            }
        }

        if ($client) {
            $client->setMealUpdated(new \DateTime());
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATED_MEAL_PLAN);
            $dispatcher->dispatch($event, Event::TRAINER_UPDATED_MEAL_PLAN);
        }

        //check if user has a pre-made description template
        //that we can apply + that the meal plan is an auto-generated one
        if ($containsAlternatives && $client) {
            $defaultMessages = $this
                ->defaultMessageRepository
                ->getByUserAndType($user, DefaultMessage::TYPE_PDF_MEAL_PLANS_INTRO, [], null, true);

            if ($defaultMessages->count() > 0) {
                $placeholders = [
                    '[client]' => $client->getFirstName(),
                    '[kcals]' => $desiredKcals
                ];

                $message = $this
                    ->defaultMessageService
                    ->replaceMessageWithActualValues($defaultMessages->last()['message'], $placeholders);

                $newPlan->setExplaination($message);
            }
        }

        $this->em->flush();

        return $newPlan;
    }

    public function getMasterMealPlanMeta(MasterMealPlan $plan, int $duration): MasterMealPlanMeta
    {
        $masterMealPlanMeta = $plan->getMasterMealPlanMeta();
        if($masterMealPlanMeta === null) {
            $masterMealPlanMeta = new MasterMealPlanMeta($plan, $duration);
            $masterMealPlanMeta->setPlan($plan);
            $this->em->persist($masterMealPlanMeta);
            $this->em->flush();
            return $masterMealPlanMeta;
        }

        return $masterMealPlanMeta;
    }

    /**
  	 * @param MasterMealPlan $plan
  	 * @param $clients
  	 * @return array
  	 * @throws \Doctrine\ORM\ORMException
  	 * @throws \Doctrine\ORM\OptimisticLockException
  	 * @throws \Doctrine\ORM\TransactionRequiredException
  	 */
  	public function assignPlanToClients(MasterMealPlan $plan, $clients)
    {
    		$response = [];
    		foreach ($clients as $clientId) {
    			$client = $this->em->find(Client::class, $clientId);
    			if ($client) {
    				$name = $plan->getName();
    				$comment = $plan->getExplaination();
    				try {
    					$this->createMasterPlan($name, $comment, $client->getUser(), $client, $plan);
    					$client->setMealUpdated(new \DateTime());
    					$response[] = [
    						'assigned' => true,
    						'clientId' => $clientId,
    						'clientName' => $client->getName()
    					];
    				} catch (\Exception $e) {
    					$response[] = [
    						'assigned' => false,
    						'clientId' => $clientId,
    						'error' => $e->getMessage()
    					];
    				}
    			}
    		}
    		$this->em->flush();

    		return $response;
  	}

    /**
     * @deprecated
     * @param MasterMealPlan $plan
     * @param MealPlan $meal
     * @param array $foodPreferences
     * @param int $mealId
     * @param array $ingredientsToExclude
     *
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function replaceRecipe(MasterMealPlan $plan, MealPlan $meal, array $foodPreferences = [], $mealId = null, array $ingredientsToExclude = [])
    {
        $repo = $this->em->getRepository(Recipe::class);
        $type = $meal->getType();

        if ($mealId) {
            $newMeal = $mealId;
        } else {
            $params = [
                'type'                  => $type,
                'locale'                => $plan->getLocale(),
                'macroSplit'            => $plan->getMacroSplit(),
                'userId'                => $this->getUser()->getId(),
                'ingredientsToExclude'  => $ingredientsToExclude,
                'foodPreferences'       => $foodPreferences,
                'onlyIds'               => true,
                'groupBy'               => 'type'
            ];

            $recipes = $repo->getAllRecipes($params);
            $scoreMap = $repo::getScoreMapForRandomizer($recipes);
            $recipes = $repo::transformForRandomizer($recipes);

            $recipesByType = isset($recipes[$type]) ? $recipes[$type] : [];
            $newMeal = $this->getRandomRecipe($recipesByType, [], $scoreMap);
        }

        $newMealEntity = $repo->find($newMeal);
        if ($newMealEntity === null) {
            throw new NotFoundHttpException();
        }
        $newMealPlan = $this->copyRecipe($newMealEntity, $plan, $meal->getOrder(), $meal->getParent());

        $oldMealKcal = $meal->getTotals()['kcal'];
        $newMealKcal = $this->getKcals($newMeal);

        $ratio = $oldMealKcal / $newMealKcal;

        $products = $newMealEntity->getProducts();
        foreach ($products as $product) {
            $newProduct = $this->createProductFromRecipe($product);
            $this->adjustFoodProductWeight($newMealPlan, $ratio, $newProduct);
        }

        $this->em->remove($meal);
        $this->em->flush();

        $products = $this->serializeMealProducts($newMealPlan);

        $newRecipe = $newMealPlan->getRecipe();
        if ($newRecipe === null) {
            throw new \RuntimeException('Something went wrong');
        }

        return [
            'id' => $newMealPlan->getId(),
            'name' => $newMealPlan->getName(),
            'order' => $newMealPlan->getOrder(),
            'comment' => $newMealPlan->getComment(),
            'image' => $newMealPlan->getImage(),
            'products' => $products,
            'locale' => $plan->getLocale(),
            'macro_split' => $newMealPlan->getMacroSplit(),
            'recipe_id' => $newRecipe->getId(),
            'type' => $newMealPlan->getType(),
            'totals' => $newRecipe->getTotals(),
            'total_kcals' => $plan->getKcals(),
            'plan' => $plan->getId()
        ];
    }

    /**
     * @deprecated
     * @param MasterMealPlan $plan
     * @param int $type
     * @param array $foodPreferences
     * @param int $mealId
     * @param array $ingredientsToExclude
     *
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function addRecipe(MasterMealPlan $plan, $type, array $foodPreferences = [], $mealId = null, array $ingredientsToExclude = [])
    {
        $repo = $this->em->getRepository(Recipe::class);
        $mealPlanRepo = $this->em->getRepository(MealPlan::class);

        $newMeal = $mealId;
        if(!$newMeal) {
            $params = [
                'type'                  => $type,
                'locale'                => $plan->getLocale(),
                'macroSplit'            => $plan->getMacroSplit(),
                'userId'                => $this->getUser()->getId(),
                'ingredientsToExclude'  => $ingredientsToExclude,
                'foodPreferences'       => $foodPreferences,
                'onlyIds'               => true,
                'groupBy'               => 'type'
            ];
            $recipes = $repo->getAllRecipes($params);
            $scoreMap = $repo::getScoreMapForRandomizer($recipes);
            $recipes = $repo::transformForRandomizer($recipes);

            $newMeal = $this->getRandomRecipe($recipes, [], $scoreMap);
        }

        $mealEntity = $repo->find($newMeal);
        if ($mealEntity === null) {
            throw new NotFoundHttpException();
        }
        $totalPlans = count($plan->getRecipes());
        $numberOfMeals = $totalPlans + 1;
        // Get the amount of kcals that we want to hit
        $desiredKcal = $plan->getDesiredKcals();

        $parent = $mealPlanRepo->findOneBy([
            'masterMealPlan' => $plan->getId(),
            'parent' => null
        ]);

        $newMealPlan = $this->copyRecipe($mealEntity, $plan, $numberOfMeals, $parent);

        $newMealKcal = $this->getKcals($newMeal);
        $total_kcals = $plan->getKcals();

        if (isset(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$type])) {
            $pct = MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$type];
        } else {
            $pct = 1 / $numberOfMeals;
        }

        $desiredMealKcal = $pct*$total_kcals;
        $ratio = $this->calculateRatio($desiredMealKcal, $newMealKcal);

        $products = $mealEntity->getProducts();
        foreach ($products as $product) {
            $newProduct = $this->createProductFromRecipe($product);
            $this->adjustFoodProductWeight($newMealPlan, $ratio, $newProduct);
        }

        $this->em->flush();
        $products = $this->serializeMealProducts($newMealPlan);

        $newRecipe = $newMealPlan->getRecipe();
        if ($newRecipe === null) {
            throw new \RuntimeException('Something went wrong');
        }

        return [
            'id' => $newMealPlan->getId(),
            'name' => $newMealPlan->getName(),
            'order' => $newMealPlan->getOrder(),
            'comment' => $newMealPlan->getComment(),
            'image' => $newMealPlan->getImage(),
            'products' => $products,
            'locale' => $plan->getLocale(),
            'macro_split' => $newMealPlan->getMacroSplit(),
            'recipe_id' => $newRecipe->getId(),
            'type' => $newMealPlan->getType(),
            'totals' => $newMealPlan->getTotals(),
            'total_kcals' => $total_kcals,
            'plan' => $plan->getId()
        ];
    }

    private function calculateRatio($desired, $meal)
    {
        return ($meal == 0 || $desired == 0) ? 1 : $desired / $meal;
    }

    public function updatePlanHelper(MasterMealPlan $plan, $desiredKcal, $totalKcals = null)
    {
        $ratio = $totalKcals ? $desiredKcal / $totalKcals : $desiredKcal / $plan->getKcals();
        foreach ($plan->getRecipes() as $recipe) {
            // Get total kcals in the meal / recipe
            $mealPlanProducts = $recipe->getProducts();
            foreach ($mealPlanProducts as $mealPlanProduct) {
                // Apply the new recipe ratio to the meal products in the plan
                $this->adjustFoodProductWeight(null, $ratio, $mealPlanProduct);
            }
        }

        $this->em->flush();
    }

    private function getRandomRecipe($recipes, array $skipRecipes = [], $scoreMap = [])
    {
        $randomPool = $this->recipeBaseService->randomRecipes($recipes, $skipRecipes, 1, $scoreMap);
        return Arr::first($randomPool);
    }

    private function getKcals($recipeId)
    {
        $recipe = $this->em->getRepository(Recipe::class)->find($recipeId);
        if ($recipe === null) {
            throw new NotFoundHttpException();
        }
        return $recipe->getKcals();
    }

    /**
     * @deprecated
     * @param MasterMealPlan $plan
     * @param array $foodPreferences
     * @param array $mealTypes
     * @param array $ingredientsToExclude
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateMealPlanFromKcalAndPreferences(MasterMealPlan $plan, array $foodPreferences, array $mealTypes, array $ingredientsToExclude = [])
    {
        $repo = $this->em->getRepository(Recipe::class);
        $params = [
            'locale'                => $plan->getLocale(),
            'macroSplit'            => $plan->getMacroSplit(),
            'userId'                => $this->getUser()->getId(),
            'ingredientsToExclude'  => $ingredientsToExclude,
            'foodPreferences'       => $foodPreferences,
            'onlyIds'               => true,
            'groupBy'               => 'type'
        ];
        $recipes = $repo->getAllRecipes($params);
        $scoreMap = $repo::getScoreMapForRandomizer($recipes);
        $recipes = $repo::transformForRandomizer($recipes);

        $desiredKcal = $plan->getDesiredKcals();
        $parent = $this->createPlan($plan->getName(), $plan, 1, false);
        $totalKcals = null;

        $meals = [];
        $recipeIds = [];
        foreach ($mealTypes as $type) {
            $recipesByType = isset($recipes[$type['type']]) ? $recipes[$type['type']] : [];
            $recipeId = $this->getRandomRecipe($recipesByType, $recipeIds, $scoreMap);
            $recipeIds[] = $recipeId;
            $meals[] = [
              'meal' => $recipeId,
              'percentage' => $type['percentage']
            ];
            $totalKcals += $this->getKcals($recipeId);
        }

        $i = 1;
        $overallRatio = $this->calculateRatio($desiredKcal, $totalKcals);
        foreach ($meals as $meal) {
            $recipeEntity = $repo->find($meal['meal']);
            if ($recipeEntity === null) {
                throw new NotFoundHttpException();
            }
            $newMealPlan = $this->copyRecipe($recipeEntity, $plan, $i, $parent);

            $mealRatio = $totalKcals * $meal['percentage'] / $this->getKcals($meal['meal']) * $overallRatio;

            $products = $recipeEntity->getProducts();
            foreach ($products as $product) {
                $newProduct = $this->createProductFromRecipe($product);
                $this->adjustFoodProductWeight($newMealPlan, $mealRatio, $newProduct);
            }

            $i++;
        }

        $this->em->flush();
    }

    private function createProductFromRecipe(RecipeProduct $recipeProduct) {
        $mealPlanProduct = new MealPlanProduct($recipeProduct->getProduct());
        $mealPlanProduct
          ->setTotalWeight($recipeProduct->getTotalWeight())
          ->setOrder($recipeProduct->getOrder())
          ->setWeightUnits($recipeProduct->getWeightUnits())
          ->setWeight($recipeProduct->getWeight());

        return $mealPlanProduct;
    }

    private function adjustFoodProductWeight(?MealPlan $meal, $ratio, MealPlanProduct $mealPlanProduct): void
    {
        $weight = round($mealPlanProduct->getTotalWeight() * $ratio / 5) * 5;
        $weightUnits = round($mealPlanProduct->getWeightUnits() * $ratio * 2) / 2;

        $weight = $weight == 0 ? 1 : $weight;
        $weightUnits = $weightUnits == 0 ? 1 : $weightUnits;

        $mealPlanProductWeight = $mealPlanProduct->getWeight();
        if ($mealPlanProductWeight !== null) {
            $weight = $mealPlanProductWeight->getWeight() * $weightUnits;
        }

        $mealPlanProduct
            ->setTotalWeight((int) $weight)
            ->setWeightUnits($weightUnits);

        if ($meal) {
            $mealPlanProduct->setPlan($meal);
        }

        $this->em->persist($mealPlanProduct);
    }

    private function copyRecipe(Recipe $recipe, MasterMealPlan $plan, int $order = 1, ?MealPlan $parent = null): MealPlan
    {
        $em = $this->em;
        $newMeal = new MealPlan($plan);
        $newMeal
            ->setParent($parent)
            ->setOrder($order)
            ->setName($recipe->getName())
            ->setComment($recipe->getComment())
            ->setImage($recipe->getImage())
            ->setMacroSplit($recipe->getMacroSplit())
            ->setType($recipe->getType())
            ->setRecipe($recipe);
        $em->persist($newMeal);
        $em->flush();

        return $newMeal;
    }

    public function updateMasterPlan(User $who, MasterMealPlan $plan, Request $request): void
    {
        if (!$this->canModifyPlan($who, $plan)) {
            throw new MealPlanUnauthorized('Unable to edit meal plan.');
        }

        if ($name = (string) $request->request->get('name')) {
            $plan->setName($name);
        }

        if ($comment = (string) $request->request->get('comment')) {
            $plan->setExplaination($comment);
        }

        if ($desiredKcals = $request->request->getInt('desiredKcals')) {
            $plan->setDesiredKcals($desiredKcals);
            //we update plan kcals to hit desiredKcals
            $this->updatePlanHelper($plan, $plan->getDesiredKcals());
        }

        if (($status = $request->request->get('status')) && in_array($status, [MasterMealPlan::STATUS_ACTIVE, MasterMealPlan::STATUS_HIDDEN, MasterMealPlan::STATUS_INACTIVE], true)) {
            $plan->setStatus($status);
        }

        //--- Apply template to current workout plan

        $templates = (array) $request->request->get('templates');

        $templates = $this->getTemplatesByIds($who, $templates);
        $this->cloneMasterPlans($templates, $plan);

        $this->em->flush();
    }

    public function createPlan(?string $name, MasterMealPlan $plan, int $order = 1, bool $defaultMeals = true): MealPlan
    {
        $mealPlan = new MealPlan($plan);
        $mealPlan
            ->setName($name)
            ->setOrder($order);

        $this->em->persist($mealPlan);
        if ($defaultMeals) {
            $this->addDefaultMeals($mealPlan, $plan);
        }

        $this->em->flush();
        return $mealPlan;
    }

    /**
     * @param MasterMealPlan $plan
     * @throws MealPlanUnauthorized
     */
    public function deletePlan(User $who, MasterMealPlan $plan)
    {
        if ($this->canModifyPlan($who, $plan)) {
            $this->em->remove($plan);
            $this->em->flush();
        }

        throw new MealPlanUnauthorized('Unable to delete meal plan.');
    }

    /**
     * @param MasterMealPlan $plan
     * @param array $data
     * @param string $locale
     * @return array
     */
    public function syncPlan(MasterMealPlan $plan, array $data, $locale = 'en')
    {
        $repoMealProduct = $this->em->getRepository(MealPlanProduct::class);

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

        $meals = collect($plan->getMealPlans()->toArray())
            ->keyBy(function (MealPlan $item) {
                return $item->getId();
            });

        /**
         * Remove deleted meal plans
         */
        $removed = $meals->keys()->diff($mealList->keys());

        if ($removed->count()) {
            foreach ($removed as $id) {
                if ($meal = $meals->get($id)) {
                    $this->em->remove($meal);
                    $meals->forget($id);
                }
            }

            $this->em->flush();

            $meals = collect($plan->getMealPlans()->toArray())
                ->keyBy(function (MealPlan $item) {
                    return $item->getId();
                });
        }

        $products = collect($repoMealProduct->getByMealPlanIds($meals->keys()->all()))
            ->keyBy(function (MealPlanProduct $item) {
                return $item->getId();
            });

        /**
         * Remove deleted meal products
         *
         * @var Collection $productIds
         */
        $productIds = $mealList->reduce(function (Collection $carry, $item) {
            $products = collect(isset($item['products']) ? $item['products'] : []);
            return $carry->merge($products->pluck('entity_id'));
        }, collect());

        $removedProducts = $products->keys()->diff($productIds);

        if ($removedProducts->count() > 0) {
            foreach ($removedProducts as $id) {
                if ($product = $products->get($id)) {
                    $this->em->remove($product);
                    $products->forget($id);
                }
            }

            $this->em->flush();
        }

        $response = [
            'plans' => [],
            'meals' => []
        ];

        /**
         * @var MealPlan $meal
         */
        foreach ($meals as $index => $meal) {
            $id = $meal->getId();
            $item = collect($mealList->get($id));
            $order = (int)$item->get('order', 0);
            $parent = $item->get('parent');

            if ($order === 0) {
                $order = $index + 1;
            }

            if ($meals->has($parent)) {
                $parent = $meals->get($item->get('parent'));
            }

            $meal
                ->setComment($item->get('comment'))
                ->setOrder($order)
                ->setParent($parent)
                ->setMasterMealPlan($plan);

            /**
             * @var Collection $products
             */
            $mealProducts = [];

            if (is_array($item->get('products'))) {
                $itemProducts = collect($item->get('products'));

                foreach ($itemProducts as $productIndex => $product) {
                    /**
                     * @var MealProduct $productReference
                     */
                    $productReference = $this->em->getReference(MealProduct::class, $product['id']);

                    /**
                     * @var MealPlanProduct $mealProduct
                     */
                    $mealProduct = $products->get($product['entity_id'], new MealPlanProduct($productReference));
                    $productOrder = isset($product['order']) ? (int)$product['order'] : 0;

                    if ($productOrder === 0) {
                        $productOrder = $productIndex + 1;
                    }

                    /**
                     * @var MealProductWeight $weight
                     */
                    $weight = $product['weightId'] ?
                        $this->em->getReference(MealProductWeight::class, $product['weightId']) :
                        null;

                    $mealProduct
                        ->setOrder($productOrder)
                        ->setTotalWeight((int)$product['totalWeight'])
                        ->setPlan($meal)
                        ->setWeight($weight)
                        ->setWeightUnits((float)$product['weightUnits']);

                    if (!$mealProduct->getId()) {
                        $this->em->persist($mealProduct);
                        $this->em->flush();
                    }

                    array_push($mealProducts, $mealProduct->getId());
                }
            }

            if ($parent) {
                $response['meals'][] = [
                    'id' => $meal->getId(),
                    'totals' => $meal->getTotals(),
                    'products' => $mealProducts,
                ];
            } else {
                $response['plans'][] = [
                    'id' => $meal->getId(),
                    'totals' => $meal->getMealsTotal(),
                ];
            }
        }

        $now = new \DateTime();

        $plan->setLastUpdated($now);

        if ($client = $plan->getClient()) {
            $client->setMealUpdated($now);

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATED_MEAL_PLAN);
            $dispatcher->dispatch($event, Event::TRAINER_UPDATED_MEAL_PLAN);

            if ($client->getLocale() !== $locale) {
                $client->setLocale($locale);
            }
        }

        $this->em->flush();

        return $response;
    }

    /**
     * @param MealPlan $mealPlan
     * @param MasterMealPlan $plan
     */
    private function addDefaultMeals(MealPlan $mealPlan, MasterMealPlan $plan)
    {
        $em = $this->em;

        for ($i = 0; $i < 4; $i++) {
            $order = $i + 1;
            $mealName = 'Meal ' . $order;

            $meal = new MealPlan($plan);
            $meal
                ->setName($mealName)
                ->setParent($mealPlan)
                ->setOrder($order);

            $em->persist($meal);
        }
    }

    /**
     * @param MasterMealPlan[] $from
     * @param MasterMealPlan $to
     * @return MasterMealPlan
     */
    private function cloneMasterPlans(array $from, MasterMealPlan $to)
    {
        $count = count($from);
        $locale = null;

        if (0 === $count) {
            return $to;
        }

        $order = (int)$this->em
            ->getRepository(MealPlan::class)
            ->getLastOrderByPlan($to);

        foreach ($from as $master) {
            /**
             * @var MealPlan[] $plans
             */
            $plans = $master->getMealPlans();

            if (!$locale) {
                $locale = $master->getLocale();
            }

            /**
             * @var MealPlan $children
             * @var MealPlanProduct $product
             */
            foreach ($plans as $plan) {
                if ($plan->getParent()) {
                    continue;
                }

                $newPlan = clone $plan;
                $newPlan
                    ->setOrder(++$order)
                    ->setMasterMealPlan($to);

                $this->em->persist($newPlan);
                $this->em->flush();

                $childrenPlans = $plan->getChildren();

                foreach ($childrenPlans as $children) {
                    $newChildren = clone $children;
                    $newChildren
                        ->setParent($newPlan)
                        ->setMasterMealPlan($to);

                    $this->em->persist($newChildren);
                    $this->em->flush();

                    $products = $children->getProducts();

                    foreach ($products as $product) {
                        $newProduct = clone $product;
                        $newProduct->setPlan($newChildren);

                        $this->em->persist($newProduct);
                    }
                }

                $this->em->flush();
            }
        }

        $to->setLocale($count === 1 ? $locale : 'en');

        return $to;
    }

    private function getUser(): User
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            return $user;
        }

        throw new \RuntimeException('Not logged in');
    }

    /**
     * @param MasterMealPlan $plan
     * @return bool
     */
    public function canModifyPlan(User $who, MasterMealPlan $plan)
    {
        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($who->isAssistant() && $who->getGymAdmin()->getId() === $plan->getUser()->getId()) {
            $planClient = $plan->getClient();
            if ($planClient !== null) {
                return $planClient->getUser()->getId() === $who->getGymAdmin()->getId();
            }

            return true;
        }

        return $who->getId() === $plan->getUser()->getId();
    }

    /**
     * @param array<mixed> $ids
     * @return MasterMealPlan[]
     */
    private function getTemplatesByIds(User $user, array $ids): array
    {
        $ids = collect($ids)
            ->map(function ($value) {
                return (int)$value;
            })
            ->filter(function ($value) {
                return $value > 0;
            });

        if ($ids->count() > 0) {
            return $this->em
                ->getRepository(MasterMealPlan::class)
                ->getByIdsAndUser($ids->toArray(), $user, true);
        }

        return [];
    }

    public function serializeMealProducts(MealPlan $meal, $locale = 'en')
    {
        return array_map(function (MealPlanProduct $mealProduct) use ($locale) {
            /** @phpstan-ignore-next-line */
            return $this->serializeMealProduct($mealProduct, $locale);
        }, $meal->getProducts()->toArray());
    }

    /**
     * @deprecated
     * @param MealPlanProduct|RecipeProduct $entity
     * @param string $locale
     * @return array
     */
    public function serializeMealProduct($entity, $locale = 'en')
    {
        $product = $entity->getProduct();
        $weight = $entity->getWeight();
        $weights = $product->getWeights()->map(function (MealProductWeight $item) {
            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'weight' => $item->getWeight(),
                'locale' => $item->getLocale(),
            ];
        });

        $productNames = $locale === 'dk' ?
            array_filter([$product->getNameDanish(), $product->getName()]) :
            array_filter([$product->getName(), $product->getNameDanish()]);

        $danishName = $product->getNameDanish();
        return [
            'id' => $entity->getId(),
            'product' => [
                'id' => $product->getId(),
                'name' => StaticStringy::titleize(current($productNames)), // $product->getName(),
                'name_danish' => $danishName === null ? null : StaticStringy::titleize($danishName),
                'brand' => $product->getBrand(),
                'kcal' => $product->getKcal(),
                'kj' => $product->getKj(),
                'fat' => (float)$product->getFat(),
                'protein' => (float)$product->getProtein(),
                'carbohydrates' => (float)$product->getCarbohydrates(),
            ],
            'order' => $entity->getOrder(),
            'totalWeight' => $entity->getTotalWeight(),
            'weight' => $weight ? [
                'id' => $weight->getId(),
                'name' => $weight->getName(),
                'locale' => $weight->getLocale(),
                'weight' => $weight->getWeight(),
            ] : null,
            'weightUnits' => $entity->getWeightUnits(),
            'weights' => $weights->toArray(),
        ];
    }

    /**
     * @param MealPlan $mealPlan
     * @param UploadedFile $file
     * @param User|null $user
     * @param bool $flush
     *
     * @return MealPlan
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function attachMealPlanImage(MealPlan $mealPlan, UploadedFile $file, User $user = null, $flush = true)
    {
        if ($user) {
            abort_unless(is_owner($user, $mealPlan->getMasterMealPlan()), 403, 'Insufficient permissions to upload image');
        }

        $s3 = $this->aws->getClient();
        $s3Bucket = $this->s3ImagesBucket;
        $s3Key = $this->s3ImagesKeyPrefix . 'meals/';

        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = md5((string) $mealPlan->getId()) . '.' . $ext;

        $image = Image::make($file)
            ->orientate()
            ->fit(512, 512, function (\Intervention\Image\Constraint $constraint) {
                $constraint->upsize();
            })
            ->encode('jpg', 85);

        $s3->putObject([
            'Bucket' => $s3Bucket,
            'Key' => $s3Key . $name,
            'Body' => $image->encoded,
            'ContentType' => mime_content_type($file->getPathname())
        ]);

        $imageUrl = $this->s3beforeAfterImages . 'meals/' . $name;
        $mealPlan->setImage($imageUrl);

        if ($flush) {
            $this->em->flush();
        }

        return $mealPlan;
    }
}
