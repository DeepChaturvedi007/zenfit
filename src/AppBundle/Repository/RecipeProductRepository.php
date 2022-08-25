<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MealPlanProduct;
use AppBundle\Entity\RecipeProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeProduct[]    findAll()
 * @method RecipeProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeProductRepository extends ServiceEntityRepository
{
    /** @var class-string<RecipeProduct> */
    protected $_entityName = RecipeProduct::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @param int[] $ids
     * @return RecipeProduct[]
     */
    public function getByRecipeIds(array $ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb
            ->where($qb->expr()->in('rp.recipe', $ids))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $productIds
     *
     * @return MealPlanProduct[]
     */
    public function getByMealProductIds(array $productIds)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb
            ->where($qb->expr()->in('rp.product', $productIds))
            ->getQuery()
            ->getResult();
    }

    public function persist(RecipeProduct $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
