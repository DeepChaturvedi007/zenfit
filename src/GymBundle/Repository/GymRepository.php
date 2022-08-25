<?php declare(strict_types=1);

namespace GymBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GymBundle\Entity\Gym;
use Doctrine\Persistence\ManagerRegistry;
use AppBundle\Transformer\UserTransformer;

/**
 * @method Gym|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gym|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Gym>
 */
class GymRepository extends ServiceEntityRepository
{
    /** @var class-string<Gym> */
    protected $_entityName = Gym::class;
    private UserTransformer $userTransformer;

    public function __construct(ManagerRegistry $registry, UserTransformer $userTransformer) {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->userTransformer = $userTransformer;

        parent::__construct($registry, $this->_entityName);
    }

    public function findGymByAdmin(User $admin)
    {
        return $this->findOneBy([
            'admin' => $admin
        ]);
    }

    public function getTrainersByGym(Gym $gym) : array
    {
        return array_map(function(User $user) {
            return $this->userTransformer->transform($user);
        }, $gym->getUsers());
    }
}
