<?php

namespace App\DTO\Auth;

use App\Entity\User;

class UserResponse
{
    public int $id;
    public string $email;
    public string $name;

    public static function fromEntity(User $user): self
    {
        $response = new self();
        $response->id = $user->getId();
        $response->email = $user->getEmail();
        $response->name = $user->getName();

        return $response;
    }
}

