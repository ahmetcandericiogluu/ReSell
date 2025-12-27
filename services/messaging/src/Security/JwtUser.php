<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class JwtUser implements UserInterface
{
    public function __construct(
        private readonly int $id,
        private readonly string $email,
        private readonly ?string $name = null,
        private readonly array $roles = ['ROLE_USER']
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

