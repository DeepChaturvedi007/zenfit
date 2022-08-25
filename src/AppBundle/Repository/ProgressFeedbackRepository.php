<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ProgressFeedback as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Carbon\Carbon;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ProgressFeedbackRepository extends ServiceEntityRepository
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
     * @param ?int $limit
     * @param ?int $offset
     * @param string $order
     *
     * @return array
     */
    public function getEntriesByClient(Client $client, $limit = null, $offset = null, $order = 'ASC')
    {
        $qb = $this
            ->createQueryBuilder('pf')
            ->where('pf.client = :client')
            ->setParameter('client', $client)
            ->orderBy('pf.createdAt', $order);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb
            ->getQuery()
            ->getArrayResult();
    }

    /** @return array<string, mixed> */
    public function getAvgCheckInScoreByClient(Client $client): array
    {
        return collect($this->getEntriesByClient($client))
            ->map(function($checkIn) {
                $decodedCheckIn = json_decode($checkIn['content']);
                $date = Carbon::parse($checkIn['createdAt'])->format('Y-m-d');

                if (isset($decodedCheckIn->sliders)) {
                    $collection = collect($decodedCheckIn->sliders);
                    if (count($collection->toArray()) === count(array_filter($collection->toArray(), 'is_numeric'))) {
                        return ['score' => round($collection->avg(), 1), 'date' => $date];
                    }
                }
                return false;
            })
            ->reject(function($val) {
                return $val === false;
            })
            ->mapWithKeys(function($entry) {
                return [$entry['date'] => $entry['score']];
            })
            ->groupBy(function($key, $date) use ($client) {
                $startDate = Carbon::parse($client->getStartDate() ?? $client->getCreatedAt());
                $checkInDate = Carbon::parse($date);
                return $checkInDate->diffInWeeks($startDate);
            })
            ->map(function($entry) {
                return round($entry->avg(), 1);
            })->toArray();
    }
}
