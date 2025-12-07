<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class JwtUser implements UserInterface
{
    private int $id;
    private array $payload;

    public function __construct(int $id, array $payload)
    {
        $this->id = $id;
        $this->payload = $payload;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }
}

