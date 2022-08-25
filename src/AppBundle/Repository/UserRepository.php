<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Lead;
use AppBundle\Entity\User;
use AppBundle\Entity\User as Entity;
use ChatBundle\Entity\Conversation;
use ChatBundle\Repository\MessageRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use AppBundle\Transformer\UserTransformer;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class UserRepository extends ServiceEntityRepository
{
    /** @var class-string<User> */
    protected $_entityName = Entity::class;
    private MessageRepository $messageRepository;
    private LeadRepository $leadRepository;
    private UserTransformer $userTransformer;

    public function __construct(
        ManagerRegistry $registry,
        LeadRepository $leadRepository,
        MessageRepository $messageRepository,
        UserTransformer $userTransformer
    ) {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);

        $this->messageRepository = $messageRepository;
        $this->leadRepository = $leadRepository;
        $this->userTransformer = $userTransformer;
    }

    public function isEmailAddressTaken(string $email): bool
    {
        $result = $this->findOneBy([
            'email' => $email
        ]);

        return $result !== null;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getActiveClients($id)
    {
        $query = $this
            ->createQueryBuilder('u')
            ->select('COUNT(c.id) clients')
            ->leftJoin('u.clients','c', Join::WITH, 'c.user = u.id AND c.deleted = 0 AND c.active = 1 AND c.demoClient IS NULL')
            ->where('u.id = :user_id')
            ->setParameters([
                'user_id' => $id,
            ]);

        return $query
                ->getQuery()
                ->getSingleResult();
    }

    public function getNewLeadsCount(User $user): int
    {
        $qb = $this->leadRepository->createQueryBuilder('l');
        $qb->select('count(l.id)')
            ->andWhere('l.status = :newStatus and l.deleted=0')
            ->setParameter('newStatus', Lead::LEAD_NEW)
            ->andWhere('l.user = :user');

        if ($user->isAssistant()) {
            $qb->innerJoin('l.tags', 't')
                ->andWhere('t.title = :tagTitle')
                ->setParameter('tagTitle', $user->getFirstName())
                ->setParameter('user', $user->getGymAdmin());
        } else {
            $qb->setParameter('user', $user);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

  	public function getUnreadMessagesCount(User $user): int
    {
  		$qb = $this->messageRepository->createQueryBuilder('m');
  		$qb->select('count(m.id)')
  			->leftJoin( Conversation::class, 'c', Join::WITH, 'm.conversation = c.id' )
            ->innerJoin('m.client', 'client')
            ->andWhere('client.deleted = 0 and c.deleted = 0')
  			->andWhere('m.isNew = 1 and m.client is not null')
  			->andWhere('c.user = :user');

        if ($user->isAssistant()) {
            $qb->innerJoin('client.tags', 't')
                ->andWhere('t.title = :tagTitle')
                ->setParameter('tagTitle', $user->getFirstName())
                ->setParameter('user', $user->getGymAdmin());
        } else {
            $qb->setParameter('user', $user->getId());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
  	}

  	public function getUnreadConversationsCount(User $user): int
    {
  		$qb = $this->messageRepository->createQueryBuilder('m');
  		$qb->select('c.id')
  			->leftJoin( Conversation::class, 'c', Join::WITH, 'm.conversation = c.id' )
            ->innerJoin('m.client', 'client')
            ->addGroupBy('c.id')
            ->andWhere('client.deleted = 0 and c.deleted = 0 and client.active = 1')
  			->andWhere('m.isNew = 1 and m.client is not null')
  			->andWhere('c.user = :user');

        if ($user->isAssistant()) {
            $qb->innerJoin('client.tags', 't')
                ->andWhere('t.title = :tagTitle')
                ->setParameter('tagTitle', $user->getFirstName())
                ->setParameter('user', $user->getGymAdmin());
        } else {
            $qb->setParameter('user', $user->getId());
        }

        return count($qb->getQuery()->getArrayResult());
  	}

  	public function getWithoutUserSubscription()
    {
        $query = $this
            ->createQueryBuilder('u')
            ->leftJoin('u.userSubscription', 'us')
            ->where('us.user IS NULL')
            ->andWhere('u.activated = 1');

        return $query->getQuery()->getResult();
    }

    public function findByToken($token): User
    {
        if (!$token || $token == '') {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        $user = $this->findOneBy([
            'interactiveToken' => $token
        ]);

        if (!$user) {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        return $user;
    }

    /** @return array<Entity> */
    public function getActiveUsers(int $offset, int $limit, ?string $query, \DateTime $start = null, \DateTime $end = null, string $country = null): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb
            ->where('u.activated = 1')
            ->andWhere('u.deleted = 0');

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('u.id', 'DESC');

        if ($query !== null) {
            $qb
                ->andWhere('u.name like :query or u.email like :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($country !== null) {
            $qb->innerJoin('u.userSubscription', 'userSubscription')
                ->innerJoin('userSubscription.subscription', 'subscription')
                ->andWhere('subscription.country = :country')
                ->setParameter('country', $country);
        }

        return collect($qb->getQuery()->getResult())
            ->map(function(User $user) use ($start, $end) {
                return $this->userTransformer->transform($user, $start, $end);
            })->toArray();
    }

    public function persist(User $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
