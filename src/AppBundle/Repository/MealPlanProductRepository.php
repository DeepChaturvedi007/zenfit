<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use AppBundle\Entity\MealPlanProduct as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class MealPlanProductRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @return ResultSetMapping
     */
    private function resultSetMapping() {

        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult("id", "id")
            ->addScalarResult("plan", "plan")
            ->addScalarResult("product", "product")
            ->addScalarResult("product", "product")
            ->addScalarResult("weight", "weight")
            ->addScalarResult("order", "order");

        return $rsm;
    }

    /**
     * @param int[] $ids
     * @return Entity[]
     */
    public function getByMealPlanIds(array $ids)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where($qb->expr()->in('p.plan', $ids))
            ->getQuery()
            ->getResult();
    }

	/**
	 * @param $productIds
	 *
	 * @return Entity[]
	 */
	public function getByMealProductId($productIds)
	{
		$qb = $this->createQueryBuilder('mp');

		return $qb
			->where($qb->expr()->in('mp.product', $productIds))
			->getQuery()
			->getResult()
			;
	}
}
