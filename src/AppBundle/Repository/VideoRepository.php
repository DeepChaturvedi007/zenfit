<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\Video;
use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Video as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class VideoRepository extends ServiceEntityRepository
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

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('v')
            ->where('v.deleted = 0')
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.assignWhen', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Video[] */
    public function findByUserAndTag(User $user, $tag = ''): array
    {
        if ($tag == '') {
            $tag = '#all';
        }

        $q = $this->createQueryBuilder('v')
            ->where('v.deleted = 0')
            ->andWhere('v.user = :user');

        $q = $q
            ->setParameter('user', $user)
            ->orderBy('v.assignWhen', 'ASC');

        return collect($q->getQuery()->getResult())
            ->filter(function($video) use ($tag) {
                if ($tag === '#all') {
                    return in_array('#all', $video->getAssignmentTags())||empty($video->getAssignmentTags());
                }
                return in_array($tag, $video->getAssignmentTags());
            })
            ->toArray();
    }

    /**
     * @param User $user
     * @param array $tags
     * @return mixed
     */
    public function getVideosToAutoAssignToClientsByUser(User $user, array $tags)
    {
        // Extend tags array by #all tag
        array_push($tags, '#all');
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('v');

        $orStatements = $qb->expr()->orX();
        foreach ($tags as $tag) {
            $orStatements->add(
                $qb->expr()->like('v.assignmentTags', $qb->expr()->literal('%"' . $tag . '"%'))
            );
        }

        return $qb->join('v.user','u')
            ->where('v.user = :user')
            ->andWhere($orStatements)
            ->andWhere('v.assignmentTags IS NOT NULL')
            ->andWhere('v.deleted = 0')
            ->andWhere('v.assignWhen = 0')
            ->andWhere('v.assignmentTags != :defaultTags')
            ->setParameter('defaultTags', '{"tags":[""]}')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param string $url
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isUserAlreadyHaveVideo(User $user, $url)
    {
        $qb = $this->createQueryBuilder('v');
        return $qb->select('COUNT(v.id)')
                ->where('v.user = :user')
                ->andWhere('v.url = :url')
                ->andWhere('v.deleted = :deleted')
                ->setParameters([
                    'user' => $user,
                    'url' => $url,
                    'deleted' => false,
                ])
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
