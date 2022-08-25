<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Repository\ActivityLogRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AccountService
{
    public function __construct(private ActivityLogRepository $activityLogRepository, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @return int
     */
    public function unseenNotifications()
    {
        static $totals = null;

        if ($totals === null) {
            $totals = $this->activityLogRepository
                ->getUnseenCountByUser($this->user());
        }

        return $totals;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    private function user(): User
    {
        $user = $this->tokenStorage
            ->getToken()
            ->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('No authed user');
        }

        return $user;
    }

}
