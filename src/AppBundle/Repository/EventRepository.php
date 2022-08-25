<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    /** @var class-string<Event> */
    protected $_entityName = Event::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function findOneByName(string $name): ?Event
    {
        return $this->findOneBy([
          'name' => $name
        ]);
    }

    /**
     * @param array $names
     * @param bool $onlyIds
     *
     * @return array
     */
    public function getByNames($names = [], $onlyIds = false)
    {
        if (0 === count($names)) {
            return [];
        }

        $qb = $this->createQueryBuilder('e');

        $names = array_map(function ($str) use ($qb) {
            return $qb->expr()->eq('e.name', $qb->expr()->literal($str));
        }, $names);

        $qb->where(call_user_func_array(array($qb->expr(), 'orX'), $names));

        if ($onlyIds) {
            $qb->select('e.id');
        }

        $result = $qb
            ->getQuery()
            ->getArrayResult();

        return $onlyIds ? collect($result)->pluck('id')->all() : $result;
    }

    public function persist(Event $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
