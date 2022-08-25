<?php declare(strict_types=1);

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class CurrentUserFetcher
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    private static function getUserFromToken(?TokenInterface $token): User
    {
        if ($token !== null) {
            $user = $token->getUser();
            if ($user instanceof User) {
                return $user;
            }
        }

        throw new \RuntimeException('No user in the given token');
    }

    public function getCurrentUser(): User
    {
        return self::getUserFromToken($this->tokenStorage->getToken());
    }

    public function isLoggedIn(): bool
    {
        try {
            $this->getCurrentUser();

            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }
}
