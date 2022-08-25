<?php

namespace AppBundle\Repository;

use AppBundle\Entity\DefaultMessage;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use AppBundle\Transformer\DefaultMessageTransformer;
use AppBundle\Entity\DefaultMessage as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class DefaultMessageRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private DefaultMessageTransformer $defaultMessageTransformer;

    public function __construct(
        ManagerRegistry $registry,
        DefaultMessageTransformer $defaultMessageTransformer
    ){
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->defaultMessageTransformer = $defaultMessageTransformer;
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /** @return Collection<array> */
    public function getByUserAndType(User $user, $type, $placeholders, $locale = null, bool $autoAssign = false): Collection
    {
        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $qb = $this
            ->createQueryBuilder('dm')
            ->andWhere('dm.type = :type')
            ->setParameter('type', $type)
            ->setParameter('user', $user);

        if ($locale === null) {
            $qb->andWhere('dm.user = :user or dm.user is null');
        } else {
            $qb
                ->andWhere('dm.user = :user or (dm.user is null and dm.locale = :locale)')
                ->setParameter('locale', $locale);
        }

        if ($autoAssign) {
            $qb->andWhere('dm.autoAssign = 1');
        }

        $qb = $qb
            ->orderBy('dm.user')
            ->addOrderBy('dm.id');

        return collect($qb->getQuery()->getResult())
            ->map(function(Entity $defaultMessage) use ($placeholders) {
                return $this->defaultMessageTransformer->transform($defaultMessage, $placeholders);
            });
    }

    public function getByUser(User $user)
    {
        return collect($user->getDefaultMessages())
            ->map(function(Entity $defaultMessage){
                return $this->defaultMessageTransformer->transform($defaultMessage);
            });
    }

    public function persist(DefaultMessage $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
