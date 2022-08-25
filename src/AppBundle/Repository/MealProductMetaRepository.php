<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MealProductMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MealProductMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealProductMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealProductMeta[]    findAll()
 * @method MealProductMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<MealProductMeta>
 */
class MealProductMetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MealProductMeta::class);
    }
}
