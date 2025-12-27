<?php

namespace App\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: 'validation.email.required')]
    #[Assert\Email(message: 'validation.email.invalid')]
    public string $email;

    #[Assert\NotBlank(message: 'validation.password.required')]
    public string $password;
}

