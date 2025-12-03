<?php

namespace App\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProfileRequest
{
    #[Assert\NotBlank(message: 'İsim boş olamaz')]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 100)]
    public ?string $city = null;

    #[Assert\Length(max: 20)]
    public ?string $phone = null;
}

