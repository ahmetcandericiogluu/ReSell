<?php

namespace App\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    #[Assert\NotBlank(message: 'validation.email.required')]
    #[Assert\Email(message: 'validation.email.invalid')]
    public string $email;

    #[Assert\NotBlank(message: 'validation.password.required')]
    #[Assert\Length(
        min: 6,
        minMessage: 'validation.password.min_length'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'validation.name.required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'validation.name.min_length',
        maxMessage: 'validation.name.max_length'
    )]
    public string $name;

    #[Assert\Length(
        max: 20,
        maxMessage: 'validation.phone.max_length'
    )]
    public ?string $phone = null;

    #[Assert\Length(
        max: 100,
        maxMessage: 'validation.city.max_length'
    )]
    public ?string $city = null;
}

