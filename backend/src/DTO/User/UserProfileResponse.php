<?php

namespace App\DTO\User;

use App\Entity\User;

class UserProfileResponse
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $city;
    public ?string $phone;
    public ?float $ratingAverage;
    public string $createdAt;

    public static function fromEntity(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->name = $user->getName();
        $dto->email = $user->getEmail();
        $dto->city = $user->getCity();
        $dto->phone = $user->getPhone();
        $dto->ratingAverage = $user->getRatingAverage() ? (float) $user->getRatingAverage() : null;
        $dto->createdAt = $user->getCreatedAt()->format('Y-m-d\TH:i:s\Z');

        return $dto;
    }
}

