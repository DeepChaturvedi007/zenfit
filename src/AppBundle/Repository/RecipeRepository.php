<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Language;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipePreference;
use AppBundle\Entity\RecipeProduct;
use AppBundle\Entity\User;
use AppBundle\Enums\CookingTime;
use AppBundle\Enums\MacroSplit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    /** @var class-string<Recipe> */
    protected $_entityName = Recipe::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    static public function getScoreMapForRandomizer ($recipes, $lowPriorityForIds = [])
    {
        $scoreMap = [];
        foreach ($recipes as $recipe) {
            $isFavorite = isset($recipe['isFavorite']) ? (int) $recipe['isFavorite'] : 0;
            $isOwned = isset($recipe['isOwned']) ? (int) $recipe['isOwned'] * 1.5 : 0;
            $isLowPriority = in_array($recipe['id'], $lowPriorityForIds) ? -10 : 0;
            $scores = [ $isFavorite, $isOwned, $isLowPriority];
            $scoreMap[$recipe['id']] = array_reduce($scores, function ($carry, $item) {
                return $carry + $item;
            }, 0);
        }
        return $scoreMap;
    }

    static public function transformForRandomizer ($recipes)
    {
        $recipesCollection = collect($recipes);
        return $recipesCollection
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('id');
            })
            ->toArray();
    }

    /** @return array<string, mixed> */
    public function getIngredientsInRecipes(string $q, Language $language, ?User $user = null): array
    {
        $languageId = $language->getId();
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select(['mp.id', 'mpl.name AS name'])
            ->join('r.products', 'rp')
            ->join('rp.product', 'mp')
            ->join('mp.mealProductLanguages', 'mpl')
            ->where('r.user IS NULL or r.user = :user')
            ->andWhere('r.deleted = 0')
            ->andWhere('r.approved = 1')
            ->andWhere('IDENTITY(mpl.language) = :languageId');

        if ($q !== '') {
            $qb->andWhere($qb->expr()->like('mpl.name', $qb->expr()->literal('%'.$q.'%')));
        }

        $qb
            ->setParameters([
              'user' => $user,
              'languageId' => $languageId,
            ])
            ->orderBy('name', 'ASC')
            ->groupBy('mp.id, mpl.name');

        return $qb
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getByUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select(['r','rt'])
            ->join('r.types', 'rt')
            ->where('r.user = :user')
            ->andWhere('r.deleted = 0')
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $macroSplit
     * @return array
     */
    public function getRecipesCreatedByZenfitByMacroSplitAndLocale($macroSplit, string $locale)
    {
        return $this->createQueryBuilder('r')
            ->where('r.user IS NULL')
            ->andWhere('r.macroSplit = :macroSplit')
            ->andWhere('r.locale = :locale')
            ->andWhere('r.approved = 1')
            ->setParameters([
              'macroSplit' => $macroSplit,
              'locale' => $locale
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getAllRecipes(array $params = []): array
    {
        $getProperty = function ($key, $default = null) use ($params) {
            return isset($params[$key]) ? $params[$key] : $default;
        };

        // Querying
        $q                      = $getProperty('q', null);
        $type                   = $getProperty('type', null);
        $locale                 = $getProperty('locale', null);
        $foodPreferences        = $getProperty('foodPreferences', []);
        $macroSplit             = $getProperty('macroSplit', null);
        $cookingTime            = $getProperty('cookingTime', null);
        $userId                 = (int) $getProperty('userId');

        // Extra options and sub queries
        $onlyIds                = $getProperty('onlyIds', false);
        $favorites              = $getProperty('favorites', false);
        $considerIngredients    = $getProperty('considerIngredients', false);
        $filterByUser           = $getProperty('filterByUser', false);
        $prioritizeByUser       = $getProperty('prioritizeByUser', false);
        $ingredientsToExclude   = $getProperty('ingredientsToExclude', []);

        // Pagination & Sorting
        $maxResults             = $getProperty('limit');
        $firstResult            = $getProperty('offset');
        $orderBy                = $getProperty('orderBy', null);
        $groupBy                = $getProperty('groupBy', null);


        $qb = $this->createQueryBuilder('r');

        $select = [
            'r.id',
            'r.name',
            'r.image',
            'r.cookingTime',
            'r.macroSplit',
            'r.createdAt',
            'rm.lactose',
            'rm.gluten',
            'rm.nuts',
            'rm.eggs',
            'rm.pig',
            'rm.shellfish',
            'rm.fish',
            'rm.isVegetarian',
            'rm.isVegan',
            'rm.isPescetarian'
        ];

        $qb
            ->select($select)
            ->leftJoin('r.recipeMeta', 'rm')
            ->leftJoin('r.types', 'rt')
            ->leftJoin('r.preferences', 'rp', Join::WITH, 'rp.user = :user')
            ->where('r.locale = :locale')
            ->andWhere($filterByUser ? 'r.user = :user' : 'r.user IS NULL or r.user = :user')
            ->andWhere('r.deleted = 0')
            ->andWhere('r.approved = 1')
            ->andWhere('r.name is not null')
            ->andWhere("r.name != ''")
            ->setParameter('locale', $locale)
            ->setParameter('user', $userId)
            ->addGroupBy('r.id')
            // In order to load recipe's meta we have to group by "RecipeMeta"
            ->addGroupBy('rm');

        if (isset($params['mealPlan']) && $params['mealPlan']) {
            $qb
                ->leftJoin('r.mealPlans', 'mp', 'WITH', 'mp.recipe = r.id and mp.parent = :mealPlan and mp.deleted = 0')
                ->addOrderBy('mp.id', 'desc')
                ->addGroupBy('mp.id')
                ->setParameter('mealPlan', $params['mealPlan']);
        }

        $applyGroupBy = function ($group) use ($qb) {
            switch ($group) {
                case 'type': {
                    $qb->addSelect('rt.type')->addGroupBy('rt.type');
                    break;
                }
                default: {

                }
            }
        };

        if(is_array($groupBy)) {
            foreach ($groupBy as $group) {
                $applyGroupBy($group);
            }
        } else if (is_string($groupBy)) {
            $applyGroupBy($groupBy);
        }

        if($prioritizeByUser) {
            $isOwned = $this->createQueryBuilder('_r1')
                ->select('COUNT(_r1.id)')
                ->leftJoin('_r1.user', '_ru')
                ->where('_r1.id = r.id')
                ->andWhere('_r1.user = :user')
                ->groupBy('_ru.id')
                ->getDQL();

            $isFavorite = $this->createQueryBuilder('_r2')
                ->select('COUNT(_r2.id)')
                ->leftJoin('_r2.preferences', '_rp')
                ->where('_rp.recipe = r')
                ->andWhere('_rp.favorite = 1')
                ->getDQL();

            $qb
                ->addSelect( 'CAST(('.$isOwned.') as boolean) as isOwned')
                ->addSelect( 'CAST(('.$isFavorite.') as boolean) as isFavorite')
                ->addOrderBy('isOwned', 'DESC')
                ->addOrderBy('isFavorite', 'DESC');
        }

        if ($type) {
            $qb->andWhere($qb->expr()->eq('rt.type', $type));
        }

        if (in_array($macroSplit, MacroSplit::values())) {
            $qb->andWhere($qb->expr()->eq('r.macroSplit', $macroSplit));
        }

        if (in_array($cookingTime, CookingTime::values())) {
            $qb->andWhere($qb->expr()->eq('r.cookingTime', $cookingTime));
        }

        if ($favorites) {
            $qb->andWhere('rp.favorite = 1')
                ->andWhere('rp.user = :user')
                ->setParameter('user', $userId);
        }

        if ($q) {
            $val = "'%".strtolower($q)."%'";
            if($considerIngredients) {
                $qb
                    ->leftJoin('r.products', 'rpr')
                    ->leftJoin(
                        'AppBundle\Entity\MealProductLanguage',
                        'mpl',
                        Join::WITH,
                        "mpl.mealProduct = rpr.product"
                    )
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('LOWER(r.name)', $val),
                        $qb->expr()->like('LOWER(mpl.name)', $val)
                    ));
            } else {
                $qb->andWhere($qb->expr()->like('LOWER(r.name)', $val));
            }
        }

        $excludeVeganEtc = true;
        $disableSpecialRecipes = true;

        foreach ($foodPreferences as $ingredient => $val) {
            if (!$val) {
                continue;
            }

            if (substr($ingredient, 0, 2) == 'is') {
                //if we are looking for either vegan, vegetarian or pescetarian meals
                $qb->andWhere($qb->expr()->eq("rm.$ingredient", 1));
                $excludeVeganEtc = false;
            } else {
                // we are excluding all recipes that contain ingredients that client dislikes
                $qb->andWhere($qb->expr()->eq("rm.$ingredient", 0));
                //we allow special recipes specially built for people with lactose + gluten allergies
                if ($ingredient == 'lactose'||$ingredient == 'gluten') {
                    $disableSpecialRecipes = false;
                }
            }
        }

        if (count($ingredientsToExclude) > 0) {
            $ingredientsQB = $this
                ->getEntityManager()
                ->createQueryBuilder();

            $skippedRecipes = collect(
                $ingredientsQB
                    ->select('IDENTITY(rp.recipe) AS recipe_id')
                    ->from(RecipeProduct::class, 'rp')
                    ->where($qb->expr()->in('rp.product', $ingredientsToExclude))
                    ->groupBy('rp.recipe')
                    ->getQuery()
                    ->getArrayResult()
            )->pluck('recipe_id');

            $qb->andWhere($qb->expr()->notIn('r.id', $skippedRecipes->toArray()));
        }

        if ($userId) {
            $dislikedRecipesQB = $this
                ->getEntityManager()
                ->createQueryBuilder();

            $dislikedRecipes = collect(
                $dislikedRecipesQB
                    ->select('IDENTITY(rp.recipe) AS recipe_id')
                    ->from(RecipePreference::class, 'rp')
                    ->where('rp.user = :user')
                    ->andWhere('rp.dislike = 1')
                    ->setParameter('user', $userId)
                    ->getQuery()
                    ->getArrayResult()
            )->pluck('recipe_id');

            if ($dislikedRecipes->isNotEmpty()) {
                $qb->andWhere($qb->expr()->notIn('r.id', $dislikedRecipes->toArray()));
            }

            $qb->addSelect('rp.favorite')->addGroupBy('rp.favorite');
        }

        if ($excludeVeganEtc) {
            $qb
                ->andWhere('rm.isVegan = :skip')
                ->setParameter('skip', false);
        }

        if ($disableSpecialRecipes) {
            $qb
                ->andWhere('r.isSpecial = :isSpecial')
                ->setParameter('isSpecial', false);
        }

        if ($filterByUser) {
            $qb
                ->leftJoin('AppBundle\Entity\Recipe', 'r2', Join::WITH, 'r.user = r2.user AND r.name = r2.name AND r.id < r2.id')
                ->andWhere('r2.id IS NULL')
            ;
        }

        if($orderBy) {
            $qb->addOrderBy("r.{$orderBy}", 'ASC');
        }

        if($maxResults) {
            $qb->setMaxResults($maxResults);
        }
        if($firstResult) {
            $qb->setFirstResult($firstResult);
        }
        return $qb->getQuery()->getResult();
    }

    /** @return array<int> */
    public function getUsedRecipesIdsByClient (Client $client): array
    {
        /** @var MasterMealPlanRepository $repo */
        $repo = $this->getEntityManager()
            ->getRepository(MasterMealPlan::class);
        $pool = collect();
        /** @var MasterMealPlan $plan */
        foreach ($repo->getByClient($client) as $plan) {
            $pool = $pool->merge($plan->getRecipes());
        }
        return $pool->map(function ($meal) {
            /** @var MealPlan $meal */
            $recipe = $meal->getRecipe();
            return $recipe ? $recipe->getId() : null;
        })->filter()->toArray();
    }

    /**
     * @param array $ids
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRecipeTotalKcalsByIds(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        $sql = sprintf('
        SELECT r.id, SUM(rp.total_kcal) AS kcals FROM recipes r
LEFT JOIN (
	SELECT p.recipe_id, ROUND((mp.kcal * p.total_weight) / 100, 1) AS total_kcal
	FROM recipes_products p
	LEFT JOIN meal_products mp ON mp.id = p.meal_product_id
) rp ON rp.recipe_id = r.id
WHERE r.id IN (%s) GROUP BY r.id', implode(',', $ids));

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);

        return array_map(function ($item) {
            return [
                'id' => (int)$item['id'],
                'kcals' => round($item['kcals'], 0)
            ];
        }, $stmt->executeQuery()->fetchAllAssociative());
    }

    public function persist(Recipe $recipe): void
    {
        $this->_em->persist($recipe);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    /**
     * @param Recipe[] $recipes
     * @return array<array{id: int, date: string}>
     */
    public function getLastUsedDates(Client $client, array $recipes): array
    {
        return $this->createQueryBuilder('r', 'r.id')
            ->select('r.id as id, max(mp.createdAt) as date')
            ->leftJoin(MealPlan::class, 'mp', Join::WITH, 'r.id = mp.recipe OR r.parent = mp.recipe')
            ->leftJoin('mp.masterMealPlan', 'mmp')
            ->andWhere('mp.id is not null')
            ->andWhere('mmp.client = :client')
            ->andWhere('r.id in (:recipes)')
            ->andWhere('mp.createdAt is not null')
            ->groupBy('r.id')
            ->setParameter('recipes', $recipes)
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }
}
