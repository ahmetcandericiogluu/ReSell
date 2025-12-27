<?php

namespace App\Auth\DTO;

use App\Auth\Entity\User;

class UserResponse
{
    public int $id;
    public string $email;
    public string $name;
    public ?string $phone;
    public ?string $city;
    public string $createdAt;

    public static function fromEntity(User $user): self
    {
        $response = new self();
        $response->id = $user->getId();
        $response->email = $user->getEmail();
        $response->name = $user->getName();
        $response->phone = $user->getPhone();
        $response->city = $user->getCity();
        $response->createdAt = $user->getCreatedAt()->format('Y-m-d\TH:i:s\Z');

        return $response;
    }
}

