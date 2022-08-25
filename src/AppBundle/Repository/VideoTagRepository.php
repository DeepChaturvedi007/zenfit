<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\VideoTag as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class VideoTagRepository extends ServiceEntityRepository
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

    public function getAllUniqueTagTitlesByUser(User $user)
    {
        return $this->createQueryBuilder('ct')
            ->select(['ct.title as title'])
            ->join('ct.video', 'v')
            ->where('v.user = :user')
            ->andWhere("ct.title <> ''")
            ->groupBy('ct.title')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
    }
}
