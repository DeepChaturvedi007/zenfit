<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Document;
use AppBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Document as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class DocumentRepository extends ServiceEntityRepository
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
     * @param User $user
     * @param array $tags
     * @return mixed
     */
    public function getDocumentsToAutoAssignToClientsByUser(User $user, array $tags)
    {
        // Extend tags array by #all tag
        array_push($tags, '#all');
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('d');

        $orStatements = $qb->expr()->orX();
        foreach ($tags as $tag) {
            $orStatements->add(
                $qb->expr()->like('d.assignmentTags', $qb->expr()->literal('%"' . $tag . '"%'))
            );
        }

        return $qb->join('d.user','u')
            ->where('d.user = :user')
            ->andWhere($orStatements)
            ->andWhere('d.assignmentTags IS NOT NULL')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.assignmentTags != :defaultTags')
            ->setParameter('defaultTags', '{"tags":[""]}')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /** @return array<Document> */
    public function findByUser(User $user): array
    {
        return $this
            ->createQueryBuilder('d')
            ->where('d.deleted = 0')
            ->andWhere('d.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getDocumentsByUser(User $user, $deleted = false)
    {
        return $this->findBy([
            'user' => $user,
            'deleted' => $deleted
        ]);
    }
}
