<?php

namespace App\DTO\Listing;

use Symfony\Component\Validator\Constraints as Assert;

class ListingUpdateRequest
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $title = null;

    #[Assert\Length(min: 10)]
    public ?string $description = null;

    #[Assert\Positive]
    public ?float $price = null;

    #[Assert\Choice(choices: ['TRY', 'USD', 'EUR'])]
    public ?string $currency = null;

    #[Assert\Positive]
    public ?int $categoryId = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;

    #[Assert\Choice(choices: ['draft', 'active', 'sold', 'deleted'])]
    public ?string $status = null;
}

