<?php

namespace AppBundle\Services;

use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealPlanProduct;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\MealProductWeight;
use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Services\RecipeBaseService;
use MealBundle\Services\RecipeCustomGeneratorService;
use MealBundle\Transformer\MealProductTransformer;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipeType;
use AppBundle\Entity\RecipeProduct;
use AppBundle\Entity\RecipePreference;
use AppBundle\Entity\RecipeMeta;
use AppBundle\Entity\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RecipesService
{
    private EntityManagerInterface $em;
    private AwsService $aws;
    private AuthorizationCheckerInterface $authorizationChecker;
    private string $s3ImagesBucket;
    private string $s3ImagesKeyPrefix;
    private string $s3beforeAfterImages;
    private RecipeCustomGeneratorService $recipeCustomGeneratorService;
    private RecipeBaseService $recipeBaseService;

    public function __construct(
        EntityManagerInterface $em,
        string $s3ImagesBucket,
        string $s3ImagesKeyPrefix,
        string $s3beforeAfterImages,
        RecipeCustomGeneratorService $recipeCustomGeneratorService,
        RecipeBaseService $recipeBaseService,
        AuthorizationCheckerInterface $authorizationChecker,
        AwsService $aws
    ) {
        $this->em = $em;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->authorizationChecker = $authorizationChecker;
        $this->s3beforeAfterImages = $s3beforeAfterImages;
        $this->recipeCustomGeneratorService = $recipeCustomGeneratorService;
        $this->recipeBaseService = $recipeBaseService;
        $this->aws = $aws;
    }

    public function create(ParameterBag $params, User $user = null, ?Recipe $fromRecipe = null, bool $flush = true): Recipe
    {
        $em = $this->em;
        $mode = $params->get('mode', 'create');

        if ($fromRecipe === null) {
            $name = $params->get('name');
            $locale = $params->get('locale');
            if ($name === null || $locale === null) {
                throw new \RuntimeException('Please provide name and locale for a recipe');
            }
            $recipe = new Recipe($name, $locale);
        }

        switch ($mode) {
            case 'clone':
            case 'save': {
                if ($fromRecipe !== null) {
                    $recipe = clone $fromRecipe;
                }
                $recipe->setUser($user);
                $em->persist($recipe);

                // Define new recipe meta (clone from source or create empty)
                $recipeMeta = null;
                if ($fromRecipe !== null) {
                    $fromRecipeRecipeMeta = $fromRecipe->getRecipeMeta();
                    if ($fromRecipeRecipeMeta !== null) {
                        $recipeMeta = clone $fromRecipe->getRecipeMeta();
                    }
                }

                if ($recipeMeta === null) {
                    $recipeMeta = new RecipeMeta($recipe);
                }

                $recipeMeta->setRecipe($recipe);
                $em->persist($recipeMeta);
                $recipe->setRecipeMeta($recipeMeta);
                // Define new recipe types (clone from source or create empty)
                $recipeTypes = $fromRecipe ? $fromRecipe->getTypes() : [];
                foreach ($recipeTypes as $recipeType) {
                    /** @var RecipeType $newType */
                    $newType = clone $recipeType;
                    $newType->setRecipe($recipe);
                    $this->em->persist($newType);
                    $recipe->addType($newType);
                }
                $em->flush();
                $this->update($recipe, $params, $user, true);

                // Clone the products
                $sourceMealId = $params->get('sourceMeal', null);
                if($sourceMealId) {
                    /** @var MealPlan $mealPlan */
                    $mealPlan = $em->getRepository(MealPlan::class)->find($sourceMealId);
                    $recipe->setComment($mealPlan->getComment());
                    /** @var Collection $mealPlansProducts */
                    $mealPlansProducts = $mealPlan->getProducts();
                    $this->deepCloneProducts($recipe, collect($mealPlansProducts), $flush = true);
                } else {
                    $this->copyProducts($recipe, $fromRecipe);
                }
                $this->em->flush();
                return $recipe;
            }
            case 'create':
            default: {
                if ($fromRecipe !== null) {
                    $recipe = $this->cloneRecipe($fromRecipe);
                }
                $recipe->setUser($user);
                $em->persist($recipe);
                $this->update($recipe, $params, $user);
                $this->copyProducts($recipe, $fromRecipe);

                if ($fromRecipe) {
                    $parent = $fromRecipe->getParent();
                    $recipe->setParent($parent ? $parent : $fromRecipe);
                }

                if ($flush) {
                    $this->em->flush();
                }
                return $recipe;
            }
        }
    }

    private function cloneRecipe(Recipe $fromRecipe)
    {
        $recipe = clone $fromRecipe;
        $this->cloneRecipeMeta($fromRecipe, $recipe);
        $this->cloneRecipeTypes($fromRecipe, $recipe);
        return $recipe;
    }

    private function cloneRecipeMeta(Recipe $fromRecipe, Recipe $newRecipe)
    {
        $recipeMeta = $fromRecipe->getRecipeMeta();
        if (!$recipeMeta) {
            return;
        }
        $newRecipeMeta = clone $recipeMeta;
        $newRecipeMeta->setRecipe($newRecipe);
        $this->em->persist($newRecipeMeta);
    }

    private function cloneRecipeTypes(Recipe $fromRecipe, Recipe $newRecipe)
    {
        $recipeTypes = $fromRecipe->getTypes();
        foreach ($recipeTypes as $recipeType) {
            $newType = clone $recipeType;
            $newType->setRecipe($newRecipe);
            $this->em->persist($newType);
        }
    }

    /**
     * @param Recipe $recipe
     * @param User $user
     * @return bool
     */
    public function canUpdate(Recipe $recipe, User $user)
    {
        $recipeUser = $recipe->getUser();
        return (
            $this->authorizationChecker->isGranted('ROLE_ADMIN')
                || ($recipeUser !== null && $user->getId() === $recipeUser->getId())
        );
    }

    /**
     * @param Recipe $recipe
     * @param ParameterBag $params
     * @param User $user
     * @param boolean $flush
     * @return Recipe
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(Recipe $recipe, ParameterBag $params, User $user = null, $flush = true)
    {
        if ($user) {
            abort_unless($this->canUpdate($recipe, $user), 403, 'Insufficient permissions to update');
        }

        $recipe
            ->setName($params->get('name', $recipe->getName()))
            ->setLocale($params->get('locale', $recipe->getLocale()))
            ->setMacroSplit($params->get('macro_split', $recipe->getMacroSplit()))
            ->setCookingTime($params->get('cooking_time', $recipe->getCookingTime()))
            ->setComment($params->get('comment', $recipe->getComment()));

        if ($params->get('type')) {
            $types = json_decode($params->get('type'));
            $existingTypes = $recipe->typeList();

            foreach ($types as $type) {
                if (!in_array($type, $existingTypes)) {
                    $recipeType = new RecipeType($recipe, $type);
                    $this->em->persist($recipeType);
                    $recipe->addType($recipeType);
                }
            }

            $typesToRemove = array_diff($existingTypes, $types);
            foreach ($typesToRemove as $typeToRemove) {
                $entity = $this->em->getRepository(RecipeType::class)->findOneBy([
                    'recipe' => $recipe->getId(),
                    'type' => $typeToRemove
                ]);
                if ($entity) {
                    $this->em->remove($entity);
                }
            }
        }

        if ($params->get('avoid')) {
            $rows = $this->transformFoodPreferences(json_decode($params->get('avoid')));
            $recipeMetaEntity = $recipe->getRecipeMeta();

            if (!$recipeMetaEntity) {
                $recipeMetaEntity = new RecipeMeta($recipe);
                $this->em->persist($recipeMetaEntity);
            }

            foreach ($rows as $key => $val) {
                $setter = 'set' . ucfirst($key);
                $recipeMetaEntity->$setter($val);
            }
        }

        if ($params->get('approved')) {
            if ($user === null) {
                throw new AccessDeniedHttpException('User has to be provided');
            }

            if ($this->canUpdate($recipe, $user)) {
                $recipe->setApproved((bool) $params->get('approved'));
            }
        }
        if ($flush) {
            $this->em->flush();
        }

        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @param User $user
     * @param string $option
     *
     * @return RecipePreference
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateUserRecipePreferences(Recipe $recipe, User $user, $option)
    {
        $recipePreference = $this->em
            ->getRepository(RecipePreference::class)
            ->findOneBy([
                'user' => $user,
                'recipe' => $recipe,
            ]);

        if (!$recipePreference) {
            $recipePreference = (new RecipePreference())
                ->setUser($user)
                ->setRecipe($recipe);

            $this->em->persist($recipePreference);
        }

        if ($option === 'favorite') {
            $recipePreference
                ->setFavorite(!$recipePreference->getFavorite())
                ->setDislike(false);
        } else if ($option === 'dislike') {
            $recipePreference
                ->setDislike(true)
                ->setFavorite(false);
        }

        $this->em->flush();

        return $recipePreference;
    }

    /**
     * @param array $foodPreferences
     * @return array
     */
    public function transformFoodPreferences(array $foodPreferences)
    {
        $preferences = [
            'lactose' => 'avoidLactose',
            'gluten' => 'avoidGluten',
            'nuts' => 'avoidNuts',
            'eggs' => 'avoidEggs',
            'pig' => 'avoidPig',
            'shellfish' => 'avoidShellfish',
            'fish' => 'avoidFish',
            'isVegetarian' => 'isVegetarian',
            'isVegan' => 'isVegan',
            'isPescetarian' => 'isPescetarian',
        ];

        return array_map(function ($key) use ($foodPreferences) {
            return in_array($key, $foodPreferences);
        }, $preferences);
    }

    /**
     * @param Recipe $recipe
     * @param User $user
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function remove(Recipe $recipe, User $user)
    {
        abort_unless(can_delete($user, $recipe), 403, 'Insufficient permissions to delete');

        $recipe->setDeleted(true);
        $this->em->flush();
    }

    /**
     * @param Recipe $recipe
     * @param UploadedFile $file
     * @param User $user
     * @param boolean $flush
     * @return Recipe
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function attachImage(Recipe $recipe, UploadedFile $file, User $user = null, $flush = true)
    {
        if ($user) {
            abort_unless($this->canUpdate($recipe, $user), 403, 'Insufficient permissions to upload image');
        }

        $s3 = $this->aws->getClient();
        $s3Bucket = $this->s3ImagesBucket;
        $s3Key = $this->s3ImagesKeyPrefix . 'recipes/';

        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = md5((string) $recipe->getId()) . '.' . $ext;

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

        $imageUrl = $this->s3beforeAfterImages . 'recipes/' . $name;
        $recipe->setImage($imageUrl);

        if ($flush) {
            $this->em->flush();
        }

        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @param boolean $flush
     * @return Recipe
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function detachImage(Recipe $recipe, $flush = true)
    {
        $recipe->setImage(null);

        if ($flush) {
            $this->em->flush();
        }

        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @param ParameterBag $params
     * @param User $user
     * @param boolean $flush
     * @return RecipeProduct
     * @throws \Doctrine\ORM\ORMException
     */
    public function addProduct(Recipe $recipe, ParameterBag $params, User $user, $flush = true)
    {
        abort_unless($this->canUpdate($recipe, $user), 403, 'Insufficient permissions to add product');

        $product = $this->updateProduct($recipe, null, $params, $user);
        $this->em->persist($product);

        if ($flush) {
            $this->em->flush();
        }

        return $product;
    }

    public function updateProduct(Recipe $recipe, ?RecipeProduct $product, ParameterBag $params, User $user, bool $flush = true): RecipeProduct
    {
        abort_unless($this->canUpdate($recipe, $user), 403, 'Insufficient permissions to update product');

        $mealProduct = null;
        if ($params->has('productId')) {
            /** @var ?MealProduct $mealProduct */
            $mealProduct = $this->em->getReference(MealProduct::class, $params->get('productId'));
            if ($mealProduct === null) {
                throw new NotFoundHttpException();
            }
        }

        if($product === null) {
            if ($mealProduct === null) {
                throw new NotFoundHttpException();
            }
            $product = new RecipeProduct($recipe, $mealProduct);
        }

        if ($mealProduct !== null) {
            $product->setProduct($mealProduct);
        }

        if ($params->get('weightId')) {
            /** @var ?MealProductWeight $mealProductWeight */
            $mealProductWeight = $this->em->getReference(MealProductWeight::class, $params->get('weightId'));
            if ($mealProductWeight === null) {
                throw new NotFoundHttpException();
            }
            $product->setWeight($mealProductWeight);
        }

        $product
            ->setTotalWeight((int)$params->get('totalWeight', $product->getTotalWeight()))
            ->setWeightUnits((float)$params->get('weightUnits', $product->getWeightUnits()))
            ->setOrder((int)$params->get('order', $product->getOrder()))
            ->setRecipe($recipe);

        //check if we should update RecipeMeta
        $recipeMeta = $recipe->getRecipeMeta();
        if ($product->getProduct()->getMealProductMeta() && $recipeMeta) {
            $this->updateRecipeMeta($recipeMeta, $product->getProduct());
            $flush = true;
        }

        if ($flush) {
            $this->em->flush();
        }

        return $product;
    }

    public function updateRecipeMeta(RecipeMeta $recipeMeta, MealProduct $product): void
    {
        foreach($product->serializedMealProductMeta() as $key => $val) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($recipeMeta, $setter)) {
                if ($val === true) {
                    $recipeMeta->$setter($val);
                } //we currently don't support deleting
            }

            if ($key === 'notVegetarian' && $val === true) {
                $recipeMeta->setIsVegetarian(false);
            }

            if ($key === 'notVegan' && $val === true) {
                $recipeMeta->setIsVegan(false);
            }

            if ($key === 'notPescetarian' && $val === true) {
                $recipeMeta->setIsPescetarian(false);
            }
        }
    }

    /** @param string[] $without */
    public function copyProducts(Recipe $recipe, Recipe $fromRecipe = null, bool $flush = true, array $without = []): Recipe
    {
        if (!$fromRecipe) {
            return $recipe;
        }

        foreach ($fromRecipe->getProducts() as $product) {

            $newProduct = clone $product;
            $newProduct->setRecipe($recipe);

            //check if we should replace ingredient
            //with a lactose-free or gluten-free alternative
            //and that this product does in fact have an alternative
            if (!empty($without)) {
                foreach ($without as $w) {
                    $getter = 'get' . ucfirst($w) . 'FreeAlternative';
                    $alternative = $product->getProduct()->$getter();

                    if ($alternative) {
                        //set new product and update recipe meta
                        $newProduct->setProduct($alternative);
                        $recipe->setIsSpecial(true);
                        $setter = 'set' . ucfirst($w);
                        $recipeMeta = $recipe->getRecipeMeta();
                        if ($recipeMeta !== null && method_exists($recipeMeta, $setter)) {
                            $recipeMeta->$setter(false);
                        }
                    }
                }
            }

            //if RecipeProduct has a weight
            //check if we can find a matching one in local language
            //else set weight = null
            $localWeight = null;
            $mealProductWeight = $newProduct->getWeight();
            if ($mealProductWeight !== null) {
                $localWeight = $this
                    ->em
                    ->getRepository(MealProductWeight::class)
                    ->findOneBy([
                        'locale' => $recipe->getLocale(),
                        'product' => $newProduct->getProduct(),
                        'weight' => $mealProductWeight->getWeight()
                    ]);
            }

            $newProduct->setWeight($localWeight);
            $this->em->persist($newProduct);
        }

        if ($flush) {
            $this->em->flush();
        }
        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @param Collection $mealPlansProducts
     * @param boolean $flush
     * @return Recipe
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deepCloneProducts(Recipe $recipe, Collection $mealPlansProducts, $flush = true)
    {
        $recipeProducts = collect($recipe->getProducts());
        $em = $this->em;
        $mealPlansProducts->each(function (MealPlanProduct $mealPlanProduct) use ($em, $recipe) {
            $newRecipeProduct = new RecipeProduct($recipe, $mealPlanProduct->getProduct());
            $newRecipeProduct->setTotalWeight($mealPlanProduct->getTotalWeight());
            $newRecipeProduct->setWeightUnits($mealPlanProduct->getWeightUnits());
            $newRecipeProduct->setOrder($mealPlanProduct->getOrder());
            $newRecipeProduct->setWeight($mealPlanProduct->getWeight());

            $em->persist($newRecipeProduct);
        });
        if ($flush) {
            $em->flush();
        }
        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @param ParameterBag $params
     * @param User $user
     * @param boolean $flush
     * @return Recipe
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function syncProducts(Recipe $recipe, ParameterBag $params, User $user, $flush = true)
    {
        abort_unless($this->canUpdate($recipe, $user), 403, 'Insufficient permissions to synchronize products');

        /**
         * @var Collection $productList
         */
        $productList = collect($params->get('products'))
            ->map(function (array $item) {
                if (isset($item['id'])) {
                    $item['productId'] = $item['id'];
                }
                $item['id'] = isset($item['entity_id']) ? $item['entity_id'] : 0;
                return new ParameterBag($item);
            });

        /**
         * @var Collection $recipeProducts
         */
        $recipeProducts = collect($recipe->getProducts())
            ->keyBy(function (RecipeProduct $item) {
                return $item->getId();
            });

        /**
         * Remove deleted recipe products
         */
        $deletedProducts = $recipeProducts
            ->keys()
            ->diff(
                $productList->map(function (ParameterBag $item) {
                    $item->get('id');
                })
            );

        if ($deletedProducts->count()) {
            foreach ($deletedProducts as $productId) {
                if ($recipeProduct = $recipeProducts->get($productId)) {
                    $this->em->remove($recipeProduct);
                    $recipeProducts->forget($productId);
                }
            }
            $this->em->flush();
        }

        /**
         * Update existing or create and
         */
        foreach ($productList as $index => $item) {
            $product = $recipeProducts->get($item->get('id'));
            $order = (int)$item->get('order', 0);

            if ($order === 0) {
                $order = $index + 1;
            }

            $item->set('order', $order);

            if ($product) {
                $this->updateProduct($recipe, $product, $item, $user, false);
            } else {
                $this->addProduct($recipe, $item, $user, false);
            }
        }


        if ($flush) {
            $this->em->flush();
        }

        return $recipe;
    }

    /**
     * @param Recipe $recipe
     * @return array
     */
    public function serializeRecipe(Recipe $recipe)
    {
        $products = collect($recipe->getProducts())
            ->map(function (RecipeProduct $product) use ($recipe) {
                return $this->serializeRecipeProduct($product, $recipe->getLocale());
            });

        $types = collect($recipe->getTypes()->toArray())
            ->map(function (RecipeType $type) {
                return [
                    'id' => $type->getId(),
                    'type' => $type->getType()
                ];
            });

        $updatedAt = $recipe->getUpdatedAt();

        return [
            'id' => $recipe->getId(),
            'name' => $recipe->getName(),
            'types' => $types,
            'recipeMeta' => $recipe->serializedRecipeMeta(),
            'locale' => $recipe->getLocale(),
            'macro_split' => $recipe->getMacroSplit(),
            'cooking_time' => $recipe->getCookingTime(),
            'comment' => $recipe->getComment(),
            'createdAt' => $recipe->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $updatedAt->format('Y-m-d H:i:s'),
            'image' => $recipe->getImage(),
            'totals' => $recipe->getTotals(),
            'products' => $products->toArray(),
        ];
    }

    /**
     *
     * @param RecipeProduct $entity
     * @param string $locale
     * @return array
     */
    public function serializeRecipeProduct($entity, $locale = 'en')
    {
        $product = $entity->getProduct();
        $weight = $entity->getWeight();

        return [
            'id' => $entity->getId(),
            'product' => (new MealProductTransformer())->transform($product, $locale),
            'order' => $entity->getOrder(),
            'totalWeight' => $entity->getTotalWeight(),
            'weight' => $weight ? [
                'id' => $weight->getId(),
                'name' => $weight->getName(),
                'locale' => $weight->getLocale(),
                'weight' => $weight->getWeight(),
            ] : null,
            'weightUnits' => $entity->getWeightUnits(),
            'weights' => $product->weightList(),
        ];
    }

    public function cloneAndAdjustRecipe(Recipe $recipe, ParameterBag $params): Recipe
    {
        $em = $this->em;
        $macroSplit         = $params->get('macro_split');
        $locale             = $params->get('locale');
        $without            = $params->get('without');
        if (array_key_exists($macroSplit, MealPlan::MACRO_SPLIT)) {
            $macros = MealPlan::MACRO_SPLIT[$macroSplit];
        } else {
            throw new \Exception('Unable to detect macro desired info');
        }
        // Clone recipe

        $newRecipe = clone $recipe;
        $newRecipe
            ->setName($recipe->getName())
            ->setParent($recipe)
            ->setComment($recipe->getComment())
            ->setLocale($locale)
            ->setMacroSplit($macroSplit)
            ->setApproved(false);

        try {
            $em->persist($newRecipe);
            $em->flush();

            // Clone meta & recipes
            $this->cloneRecipeTypes($recipe, $newRecipe);
            $this->cloneRecipeMeta($recipe, $newRecipe);
            $em->flush();
            $em->refresh($newRecipe);

            $this->copyProducts($newRecipe, $recipe, false, $without);
            $em->flush();
            $em->refresh($newRecipe);

            // Adjust macro split
            //get total kcals in recipe
            $totalKcals = $newRecipe->getKcals();

            //create array with desired number of protein, carbs, and fat
            $macros = [
                'carbohydrate' => round($macros['carbohydrate'] * $totalKcals / 4),
                'protein' => round($macros['protein'] * $totalKcals / 4),
                'fat' => round($macros['fat'] * $totalKcals / 9)
            ];

            $currentRecipes = [];
            $attempt = 0;

            $result = $this
                ->recipeCustomGeneratorService
                ->attemptToHitMacros($newRecipe, $macros, $newRecipe->getType(), $currentRecipes, $attempt, false);

            $ratio = $result['ratios'];

            $recipeBaseService = $this->recipeBaseService;
            //loop through all ingredients in the meal and apply ratio
            foreach($newRecipe->getProducts() as $product) {
                $ingRatio = $recipeBaseService->getRatio($ratio, $product);
                $recipeBaseService->adjustFoodProductWeight(null, (int) $ingRatio, $product);
            }

            $em->flush();
            $em->refresh($newRecipe);

            return $newRecipe;
        } catch (\Exception $e) {
            $newRecipe->setName($newRecipe->getName() . ' [NEED ATTENTION]');
            $em->persist($newRecipe);
            $em->flush();
            return $newRecipe;
        }
    }
}
