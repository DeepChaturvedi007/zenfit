<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MealProductLanguage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MealProductLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealProductLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealProductLanguage[]    findAll()
 * @method MealProductLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealProductLanguageRepository extends ServiceEntityRepository
{
    /** @var class-string<MealProductLanguage> */
    protected $_entityName = MealProductLanguage::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function persist(MealProductLanguage $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
