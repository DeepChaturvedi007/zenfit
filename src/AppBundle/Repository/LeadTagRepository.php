<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\LeadTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LeadTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeadTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeadTag[]    findAll()
 * @method LeadTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeadTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadTag::class);
    }

    public function getAllTagsByUser(User $user): array
    {
        $gyms = $user->getGyms();
        $tags = [];

        if (array_key_exists(0, $gyms)) {
            if ($user === $gyms[0]->getAdmin()) {
                /** @var User $user */
                foreach ($gyms[0]->getUsers() as $gymUser) {
                    $tags[$gymUser->getFirstName()] = ['title' => $gymUser->getFirstName()];
                }
            }
        }

        return array_values($tags);
    }
}
