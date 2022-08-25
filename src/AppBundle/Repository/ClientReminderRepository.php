<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ClientReminder;
use AppBundle\Entity\ClientReminder as Entity;
use AppBundle\Transformer\ClientReminderTransformer;
use AppBundle\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;

/**
 * @method ClientReminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientReminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientReminder[]    findAll()
 * @method ClientReminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientReminderRepository extends ServiceEntityRepository
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
     * @param Client $client
     * @return Collection<mixed>
     */
    public function findByClient(Client $client): Collection
    {
        $qb = $this
            ->createQueryBuilder('cr')
            ->where('cr.client = :client')
            ->andWhere('cr.deleted = 0')
            ->orderBy('cr.dueDate', 'ASC')
            ->setParameter('client', $client);

        return collect($qb->getQuery()->getResult())
						->map(function(ClientReminder $cm) {
								return (new ClientReminderTransformer())->transform($cm);
						});
    }
}
