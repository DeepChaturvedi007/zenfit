<?php

namespace AdminBundle\Controller;

use AdminBundle\Transformer\MealProductTransformer;
use AppBundle\Services\RecipesService;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Services\LeadService;
use AppBundle\Controller\Controller;
use AppBundle\Entity\Language;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\MealProductLanguage;
use AppBundle\Entity\MealProductWeight;
use AppBundle\Repository\ClientRepository;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Transformer\LeadTransformer;
use Stripe;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiController extends Controller
{
    private RecipesService $recipesService;
    private Stripe\Stripe $stripe;
    private ClientRepository $clientRepository;
    private string $appHostname;

    public function __construct(
        Stripe\Stripe $stripe,
        RecipesService $recipesService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        ClientRepository $clientRepository,
        string $appHostname
    ) {
        $this->recipesService = $recipesService;
        $this->stripe = $stripe; //has to be injected for setting up api key
        $this->clientRepository = $clientRepository;
        $this->appHostname = $appHostname;

        parent::__construct($em, $tokenStorage);
    }

    public function usersAction(Request $request)
    {
        $maxResults = $request->query->getInt('maxResults') ?? $request->query->getInt('limit', 200);
        $offset = $request->query->getInt('offset');
        $name = $request->query->get('name');
        $email = $request->query->get('email');
        $activeOnly = $request->query->getBoolean('activeOnly', false);
        $failedPayment = $request->query->getBoolean('failedPayment');
        $newUsers = $request->query->getBoolean('new');
        $sortField = $request->query->get('sort', 'signupDate');


        $now = new Carbon();

        $startOfThisMonth = (clone $now)->startOfMonth();
        $startOfPrevMonth = (clone $now)->subMonth()->startOfMonth();

        $users = $this
            ->getEm()
            ->getRepository(User::class)
            ->createQueryBuilder('u', 'u.id')
            ->select([
                'u.id',
                'u.name',
                'u.lastLogin',
                'u.email',
                'u.activated',
                'userStripe.stripeUserId AS stripe_connect',
                'u.interactiveToken',
                'u.signupDate as signupDate',
                's.title AS sub',
                'us.subscribedDate',
                'us.subscribedDate AS sub_date',
                'us.canceled AS sub_canceled',
                'us.lastPaymentFailed AS sub_last_payment_failed',
                'us.nextPaymentAttempt as sub_next_payment_attempt',
                'us.stripeCustomer as stripe_customer',
                'us.invoiceUrl as sub_next_invoice_url',
                '(
                    SELECT count(t2.id)
                    FROM AppBundle\Entity\Client t2
                    WHERE t2.user = u.id
                      AND t2.active = 1
                      AND (t2.deleted = 0 OR t2.deleted IS NULL)
                      AND (t2.demoClient = 1 OR t2.demoClient IS NULL)
                ) AS clients',
                '(
                    SELECT count(t3.id)
                    FROM AppBundle\Entity\Client t3
                    WHERE t3.user = u.id
                      AND t3.active = 1
                      AND (t3.deleted = 0 OR t3.deleted IS NULL)
                      AND (t3.demoClient = 1 OR t3.demoClient IS NULL)
                      AND t3.createdAt BETWEEN :startOfThisMonth AND :now
                ) AS clients_this_mont',
                '(
                    SELECT count(t4.id)
                    FROM AppBundle\Entity\Client t4
                    WHERE t4.user = u.id
                      AND t4.active = 1
                      AND (t4.deleted = 0 OR t4.deleted IS NULL)
                      AND (t4.demoClient = 1 OR t4.demoClient IS NULL)
                      AND t4.createdAt BETWEEN :startOfPrevMonth AND :startOfThisMonth
                ) AS clients_last_mont',
            ])
            ->join('u.userSubscription', 'us')
            ->leftJoin('u.userStripe', 'userStripe')
            ->join('us.subscription', 's')
            ->setParameter('now', $now)
            ->setParameter('startOfThisMonth', $startOfThisMonth)
            ->setParameter('startOfPrevMonth', $startOfPrevMonth)
            ->orderBy($sortField, 'DESC');

        if (!empty($name)) {
            $users = $users->andWhere('u.name=:name');
            $users->setParameter('name', $name);
        }

        if (!empty($email)) {
            $users = $users->andWhere('u.email=:email');
            $users->setParameter('email', $email);
        }

        if (!empty($activeOnly) && $activeOnly) {
            $users = $users->andWhere('u.activated=:activated');
            $users->setParameter('activated', intval($activeOnly));
        }

        if (!empty($failedPayment) && $failedPayment) {
            $users = $users
                ->andWhere('us.lastPaymentFailed=:failedPayment')
                ->andWhere('us.invoiceUrl is not null');
            $users->setParameter('failedPayment', intval($failedPayment));
        }

        if(!empty($newUsers) && $newUsers) {
            $twoWeeksAgo = (new Carbon())->subWeeks(2);
            $users->andWhere('u.signupDate >= :twoWeeksAgo');
            $users->andWhere('u.activated = 1');
            $users->setParameter('twoWeeksAgo', $twoWeeksAgo);
        }

        $usersResult = $users
            ->setMaxResults($maxResults)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();

        $users = [];
        foreach ($usersResult as $user) {
            // stringify the dates
            foreach ($user as $key => $value) {
                if($value instanceof \DateTime) {
                    $user[$key] = $value->format('Y-m-d H:i:s');
                }
            }
            array_push($users, $user);
        }
        return new JsonResponse($users);
    }

    public function activateTrainerAction(Request $request)
    {
        $user = $this
            ->getEm()
            ->getRepository(User::class)
            ->find($request->request->get('user'));

        if ($user) {
            $user->setActivated(true);
            $this->getEm()->flush();
        }

        return new JsonResponse('OK');
    }

    public function getRecipesAction(Request $request)
    {
        $input      = $request->query;
        $limit      = $input->getInt('limit', 20);
        $offset     = $input->getInt('offset', 0);
        $type       = $input->get('type', '');
        $duration   = $input->get('duration', '');
        $locale     = $input->get('locale', '');
        $name       = $input->get('name', '');
        $macroSplit = $input->get('macroSplit', '');
        $approved   = $input->get('approved', '');

        $queryBuilder = $this->getEm()
            ->getRepository(Recipe::class)
            ->createQueryBuilder('r')
            ->join('r.recipeMeta', 'meta');

        if(!!$name) {
            $queryBuilder->andWhere('LOWER(r.name) LIKE :name')
                ->setParameter('name', "%{$name}%");
        }

        if(is_numeric($approved)) {
            $queryBuilder->andWhere('r.approved = :approved')
                ->setParameter('approved', (int) $approved);
        }

        if(!!$type) {
            $queryBuilder
                ->join('r.types', 'types')
                ->andWhere('types.type = :type')
                ->setParameter('type', $type);
        }
        if(!!$duration) {
            $queryBuilder->andWhere('r.cookingTime = :cookingTime')
                ->setParameter('cookingTime', $duration);
        }
        if(!!$locale) {
            $queryBuilder->andWhere('r.locale = :locale')
                ->setParameter('locale', $locale);
        }
        if(!!$macroSplit) {
            $queryBuilder->andWhere('r.macroSplit = :macroSplit')
                ->setParameter('macroSplit', $macroSplit);
        }

        if($input->has('lactose')) {
            $lactose = $input->getBoolean('lactose', true);
            $queryBuilder
                ->andWhere('meta.lactose = :lactose')
                ->setParameter('lactose', (int) $lactose);
        }
        if($input->has('gluten')) {
            $gluten = $input->getBoolean('gluten', true);
            $queryBuilder
                ->andWhere('meta.gluten = :gluten')
                ->setParameter('gluten', (int) $gluten);
        }
        if($input->has('nuts')) {
            $nuts = $input->getBoolean('nuts', true);
            $queryBuilder
                ->andWhere('meta.nuts = :nuts')
                ->setParameter('nuts', (int) $nuts);
        }
        if($input->has('eggs')) {
            $eggs = $input->getBoolean('eggs', true);
            $queryBuilder
                ->andWhere('meta.eggs = :eggs')
                ->setParameter('eggs', (int) $eggs);
        }
        if($input->has('pig')) {
            $pig = $input->getBoolean('pig', true);
            $queryBuilder
                ->andWhere('meta.pig = :pig')
                ->setParameter('pig', (int) $pig);
        }
        if($input->has('shellfish')) {
            $shellfish = $input->getBoolean('pig', true);
            $queryBuilder
                ->andWhere('meta.shellfish = :shellfish')
                ->setParameter('shellfish', (int) $shellfish);
        }
        if($input->has('fish')) {
            $fish = $input->getBoolean('fish', true);
            $queryBuilder
                ->andWhere('meta.fish = :fish')
                ->setParameter('fish', (int) $fish);
        }
        if($input->has('vegetarian')) {
            $vegetarian = $input->getBoolean('vegetarian', false);
            $queryBuilder
                ->andWhere('meta.isVegetarian = :vegetarian')
                ->setParameter('vegetarian', (int) $vegetarian);
        }
        if($input->has('vegan')) {
            $vegan = $input->getBoolean('vegan', false);
            $queryBuilder
                ->andWhere('meta.isVegan = :vegan')
                ->setParameter('vegan', (int) $vegan);
        }
        if($input->has('pescetarian')) {
            $pescetarian = $input->getBoolean('pescetarian', false);
            $queryBuilder
                ->andWhere('meta.isPescetarian = :pescetarian')
                ->setParameter('pescetarian', (int) $pescetarian);
        }

        $queryBuilder
            ->andWhere('r.deleted = 0')
            ->andWhere('r.parent IS NULL')
            ->andWhere('r.user IS NULL')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('r.id', 'DESC');

        $recipes = $queryBuilder
            ->getQuery()
            ->getResult();

        $rows = [];
        foreach ($recipes as $recipe) {
            $row = $this->renderView('@Admin/components/recipes/recipe_row.html.twig', [ 'recipe' => $recipe ]);
            array_push($rows, $row);
        }

        return new JsonResponse($rows);
    }

    public function showRecipeAction(Request $request, Recipe $recipe)
    {
        return new JsonResponse($this->renderView('@Admin/components/recipes/recipe_row.html.twig', [ 'recipe' => $recipe ]));
    }

    /**
     * @param Request $request
     * @param Recipe $recipe
     * @return JsonResponse
     */
    public function cloneAndAdjustRecipeAction(Request $request, Recipe $recipe)
    {
        $recipesService = $this->recipesService;
        $recipes = json_decode($request->getContent(), false);

        //we prepare array of existing recipes
        $existingRecipes = [];
        foreach ($recipe->getParentAndChildrenRecipes() as $r) {
            //check if we do in fact have ingredients in the parent recipe that is either
            //lactose or gluten-free
            $lactose = false;
            $gluten = false;
            foreach ($recipe->getProducts() as $product) {
                if ($product->getProduct()->getLactoseFreeAlternative()) {
                    $lactose = true;
                }
                if ($product->getProduct()->getGlutenFreeAlternative()) {
                    $gluten = true;
                }
            }

            $existingRecipes[$r->getLocale()][$r->getMacroSplit()] = [
                'meta' => [
                    'lactose' => $r->getRecipeMeta()->getLactose(),
                    'gluten' => $r->getRecipeMeta()->getGluten()
                ],
                'alternatives' => [
                    'lactose' => $lactose,
                    'gluten' => $gluten
                ]
            ];
        }

        try {
            $params = new ParameterBag();
            $resData = [];
            foreach ($recipes as $recipeCfg) {
                $break = false;

                $originalR = isset($existingRecipes[$recipeCfg->locale][$recipeCfg->macro_split]) ?
                    $existingRecipes[$recipeCfg->locale][$recipeCfg->macro_split] :
                    null;

                if ($originalR) {
                    foreach ($recipeCfg->without as $w) {
                        //recipe either doesn't contain lactose or gluten
                        //or recipe doesn't have the alternative ingredients necessary
                        //to convert it into a gluten-free or lactose-free version
                        if (!$this->canConvertRecipeIntoLactoseOrGlutenFreeVersion($originalR, $w)) {
                            $break = true;
                        }
                    }
                } else {
                    //we don't have the recipe available in this
                    //locale and this macro_split
                    //we check if we have one or more recipes in the specified locale
                    if (isset($existingRecipes[$recipeCfg->locale])) {
                        $existingR = $existingRecipes[$recipeCfg->locale];
                        //we get first recipe in array
                        //regardless of macro split
                        $r = array_values($existingR)[0];
                        foreach ($recipeCfg->without as $w) {
                            //recipe either doesn't contain lactose or gluten
                            //or recipe doesn't have the alternative ingredients necessary
                            //to convert it into a gluten-free or lactose-free version
                            if (!$this->canConvertRecipeIntoLactoseOrGlutenFreeVersion($r, $w)) {
                                $break = true;
                            }
                        }
                    } else {
                        //we don't have any recipes in this locale
                        //we take config from parent recipe
                        $existingR = array_values($existingRecipes)[0];
                        $r = array_values($existingR)[0];
                        foreach ($recipeCfg->without as $w) {
                            //recipe either doesn't contain lactose or gluten
                            //or recipe doesn't have the alternative ingredients necessary
                            //to convert it into a gluten-free or lactose-free version
                            if (!$this->canConvertRecipeIntoLactoseOrGlutenFreeVersion($r, $w)) {
                                $break = true;
                            }
                        }
                    }
                }

                if (empty($recipeCfg->without) && $originalR) {
                    $break = true;
                }

                if ($break) {
                    continue;
                }

                $params->set('macro_split', $recipeCfg->macro_split);
                $params->set('without', $recipeCfg->without);
                $params->set('locale', $recipeCfg->locale);
                $newRecipe = $recipesService->cloneAndAdjustRecipe($recipe, $params);
                $resData[] = [
                    'id' => $newRecipe->getId(),
                    'name' => $newRecipe->getName(),
                ];
            }
            return new JsonResponse($resData);
        } catch (HttpException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function canConvertRecipeIntoLactoseOrGlutenFreeVersion($r, $allergy)
    {
        if ($r['meta'][$allergy] && $r['alternatives'][$allergy]) {
            //recipe is able to be converted into lactose or gluten-free version
        } else {
            return false;
        }

        return true;
    }

    // Dashboard actions
    public function getStatsAction(Request $request)
    {
        $repo = $this
            ->getEm()
            ->getRepository(User::class);

        $totalCount = $repo
            ->createQueryBuilder('u', 'u.id')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $activeCount = $repo
            ->createQueryBuilder('u', 'u.id')
            ->select('count(u.id)')
            ->where('u.activated = true')
            ->getQuery()
            ->getSingleScalarResult();

        $paymentFailedCount = $repo
            ->createQueryBuilder('u', 'u.id')
            ->select('count(u.id)')
            ->join('u.userSubscription', 'us')
            ->where('us.lastPaymentFailed = true')
            ->andWhere('us.invoiceUrl is not null')
            ->getQuery()
            ->getSingleScalarResult();

        $twoWeeksAgo = (new Carbon())->subWeeks(2);
        $newTrainersCount = $repo
            ->createQueryBuilder('u', 'u.id')
            ->select('count(u.id)')
            ->where('u.signupDate >= :twoWeeksAgo')
            ->andWhere('u.activated = 1')
            ->setParameter('twoWeeksAgo', $twoWeeksAgo)
            ->getQuery()
            ->getSingleScalarResult();

        return new JsonResponse([
            'total' => (int) $totalCount,
            'active' => (int) $activeCount,
            'paymentFailed' => (int) $paymentFailedCount,
            'newTrainers' => (int) $newTrainersCount,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createIngredientAction(Request $request)
    {
        $em         = $this->getEm();
        $params     = $request->request;

        $name         = (string) $params->get('name');
        $carbs        = (float) $params->get('carbohydrate', '0');
        $fat          = (float) $params->get('fat', '0');
        $protein      = (float) $params->get('protein', '0');
        $kcal         = $params->getInt('kcals');
        $sugars       = (float) $params->get('sugars', 0);
        $satFat       = (string) $params->get('saturatedFat', '0');
        $monoFat      = (float) $params->get('monoFat', '0');
        $fiber        = (float) $params->get('fiber', '0');
        $names        = (array) $params->get('names');
        $amounts      = (array) $params->get('amounts');

        try {
            $ingredient = new MealProduct($name);
            $ingredient->setFat($fat);
            $ingredient->setProtein($protein);
            $ingredient->setCarbohydrates($carbs);
            $ingredient->setAddedSugars($sugars);
            $ingredient->setSaturatedFat($satFat);
            $ingredient->setMonoUnsaturatedFat($monoFat);
            $ingredient->setFiber($fiber);
            $ingredient->setKcal($kcal);

            $em->persist($ingredient);

            /** @var mixed $localizedName */
            foreach ($names as $localizedName) {
                if (!is_array($localizedName)) {
                    continue;
                }
                $langRepo = $em->getRepository(Language::class);
                /** @var Language $language */
                $language = $langRepo
                    ->findOneBy(['locale' => $localizedName['locale']]);
                if(!$language) {
                    throw new \Exception('Unknown locale: ' . $localizedName['locale']);
                }
                $ingredientLanguage = new MealProductLanguage($localizedName['name'], $language, $ingredient);
                $ingredientLanguage
                    ->setDeleted(false);
                $em->persist($ingredientLanguage);
            }
            /** @var mixed $localizedAmount */
            foreach ($amounts as $localizedAmount) {
                if (!is_array($localizedAmount)) {
                    continue;
                }
                $ingredientWeight = new MealProductWeight();
                $ingredientWeight
                    ->setProduct($ingredient)
                    ->setLocale((string) $localizedAmount['locale'])
                    ->setName((string) $localizedAmount['name'])
                    ->setWeight((float) $localizedAmount['value']);
                $em->persist($ingredientWeight);
            }
            $em->flush();
            $em->refresh($ingredient);
            $transformer = new MealProductTransformer();
            return new JsonResponse($transformer->transform($ingredient));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIngredientsAction(Request $request)
    {
        $params     = $request->query;
        $limit      = $params->getInt('limit', 20);
        $offset     = $params->getInt('offset', 0);
        $query      = $params->get('query', '');

        $qb = $this->getEm()
            ->getRepository(MealProduct::class)
            ->createQueryBuilder('mp');

        $val = "'%".strtolower($query)."%'";
        $ingredients = $qb
            ->select('mp')
            ->leftJoin('mp.mealProductLanguages', 'mpl')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->like('LOWER(mpl.name)', $val),
                        $qb->expr()->neq('LOWER(mpl.deleted)', 1)
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->like('LOWER(mp.name)', $val),
                        $qb->expr()->neq('LOWER(mp.deleted)', 1)
                    )
                )
            )
            ->andWhere('mp.user IS NULL')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->groupBy('mp.id')
            ->getQuery()
            ->getResult();

        $transformer = new MealProductTransformer(collect($ingredients));

        return new JsonResponse($transformer->getTransformedCollection());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateIngredientAction(Request $request)
    {
        $em           = $this->getEm();
        $params       = $request->request;

        $id           = $params->get('id');
        $name         = (string) $params->get('name');
        $carbs        = (float) $params->get('carbohydrate', '0');
        $fat          = (float) $params->get('fat', '0');
        $protein      = (float) $params->get('protein', '0');
        $kcal         = $params->getInt('kcals');
        $sugars       = (float) $params->get('sugars', '0');
        $satFat       = (string) $params->get('saturatedFat', '0');
        $monoFat      = (float) $params->get('monoFat', '0');
        $fiber        = (float) $params->get('fiber', '0');
        $names        = (array) $params->get('names');
        $amounts      = (array) $params->get('amounts');
        try {
            /** @var MealProduct $ingredient */
            $ingredient = $em->getRepository(MealProduct::class)
                ->find($id);
            $ingredient->setName($name);
            $ingredient->setFat($fat);
            $ingredient->setProtein($protein);
            $ingredient->setCarbohydrates($carbs);
            $ingredient->setKcal($kcal);
            $ingredient->setAddedSugars($sugars);
            $ingredient->setSaturatedFat($satFat);
            $ingredient->setMonoUnsaturatedFat($monoFat);
            $ingredient->setFiber($fiber);
            $em->persist($ingredient);

            $currentLanguages = $ingredient->getMealProductLanguages();
            /** @var mixed $localizedName */
            foreach ($names as $localizedName) {
                if (!is_array($localizedName)) {
                    continue;
                }
                $langRepo = $em->getRepository(Language::class);
                /** @var Language $language */
                $language = $langRepo
                    ->findOneBy(['locale' => $localizedName['locale']]);
                if(!$language) {
                    throw new \Exception('Unknown locale: ' . $localizedName['locale']);
                }
                /** @var MealProductLanguage $currentLanguage */
                $instance = collect($currentLanguages)->first(function (MealProductLanguage $currentLanguage) use ($localizedName){
                    return $localizedName['locale'] === $currentLanguage->getLanguage()->getLocale();
                });
                if(!$instance) {
                    $instance = new MealProductLanguage($localizedName['name'], $language, $ingredient);
                }
                $instance->setName($localizedName['name']);
                $em->persist($instance);
                $em->flush();
            }

            $currentWeights = $ingredient->getWeights();
            /** @var mixed $amount */
            foreach ($amounts as $amount) {
                if (!is_array($amount)) {
                    continue;
                }
                /** @var MealProductWeight $currentWeight */
                $instance = collect($currentWeights)->first(function ($currentWeight) use ($amount){
                    return $amount['locale'] === $currentWeight->getLocale();
                });
                if (!$instance) {
                    $instance = new MealProductWeight();
                    $instance->setProduct($ingredient);
                }
                $instance->setName((string)$amount['name']);
                $instance->setWeight($amount['value']);
                $instance->setLocale((string)$amount['locale']);
                $em->persist($instance);
                $em->flush();
            }

            $em->flush();
            $em->refresh($ingredient);
            $transformer = new MealProductTransformer();
            return new JsonResponse($transformer->transform($ingredient));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

    }

    /**
     * @param Request $id
     * @return JsonResponse
     */
    public function deleteIngredientAction($id)
    {
        try {
            $em = $this->getEm();
            $ingredient = $em->getRepository(MealProduct::class)
                ->findOneBy([
                    'id' => $id,
                ]);
            if ($ingredient === null) {
                throw new NotFoundHttpException('Ingridient not found');
            }
            $ingredient->setDeleted(true);

            /** @var MealProductLanguage $ingredientLanguage */
            $ingredientLanguages = $ingredient->getMealProductLanguages();
            foreach ($ingredientLanguages as $ingredientLanguage) {
                $ingredientLanguage->setDeleted(true);
            }

            $em->flush();

            return new JsonResponse();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }
    }

    public function updateRecipesChildrenAndParentAction(Recipe $recipe, Request $request): RedirectResponse
    {
        $daTitle = $request->request->get('da_title');
        $seTitle = $request->request->get('se_title');
        $noTitle = $request->request->get('no_title');
        $nlTitle = $request->request->get('nl_title');
        $fiTitle = $request->request->get('fi_title');
        $deTitle = $request->request->get('de_title');
        $enTitle = $request->request->get('en_title');

        $daDesc = $request->request->get('da_desc');
        $seDesc = $request->request->get('se_desc');
        $noDesc = $request->request->get('no_desc');
        $nlDesc = $request->request->get('nl_desc');
        $fiDesc = $request->request->get('fi_desc');
        $deDesc = $request->request->get('de_desc');
        $enDesc = $request->request->get('en_desc');

        $recipes = $recipe->getParentAndChildrenRecipes();
        foreach ($recipes as $recipe) {
            match ($recipe->getLocale()) {
                'da_DK' => $recipe
                    ->setName($daTitle)
                    ->setComment($daDesc),
                'nb_NO' => $recipe
                    ->setName($noTitle)
                    ->setComment($noDesc),
                'sv_SE' => $recipe
                    ->setName($seTitle)
                    ->setComment($seDesc),
                'nl_NL' => $recipe
                    ->setName($nlTitle)
                    ->setComment($nlDesc),
                'fi_FI' => $recipe
                    ->setName($fiTitle)
                    ->setComment($fiDesc),
                'de_DE' => $recipe
                    ->setName($deTitle)
                    ->setComment($deDesc),
                default => $recipe
                    ->setName($enTitle)
                    ->setComment($enDesc),
            };
        }

        $this->getEm()->flush();
        return new RedirectResponse($request->headers->get('referer'));
    }

    public function getClientsAction(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $q = $request->query->get('q');

        if ($q == '') {
            return new JsonResponse([]);
        }

        $qb = $this
            ->clientRepository
            ->createQueryBuilder('c');

        $word = $qb->expr()->literal('%' . $q . '%');
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('c.name', $word),
                $qb->expr()->like('c.email', $word)
            )
        );

        $qb = $qb->setMaxResults(20);

        $leads = collect($qb->getQuery()->getResult())
            ->map(function(Client $client) {
                return [
                    'id' => $client->getId(),
                    'name' => $client->getName(),
                    'email' => $client->getEmail(),
                    'hasBeenActivated' => $client->hasBeenActivated(),
                    'accessApp' => $client->getAccessApp(),
                    'activationUrl' => $client->getClientURL(false, $this->appHostname),
                    'questionnaireUrl' => $client->getClientURL(true, $this->appHostname)
                ];
            });

        return new JsonResponse($leads);
    }
}
