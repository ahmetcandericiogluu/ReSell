<?php

namespace App\DTO\Listing;

use Symfony\Component\Validator\Constraints as Assert;

class ListingCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $price;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['TRY', 'USD', 'EUR'])]
    public string $currency = 'TRY';

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $categoryId;

    #[Assert\Length(max: 255)]
    public ?string $location = null;

    #[Assert\Choice(choices: ['draft', 'active'])]
    public string $status = 'active';

    /** @var string[] */
    public array $imageUrls = [];
}

