<?php

namespace App\Shared\Security;

use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Safely retrieves the currently authenticated user
 */
class CurrentUserProvider
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function getUser(): ?User
    {
        $user = $this->security->getUser();
        
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }

        return $user;
    }
}

