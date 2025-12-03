<?php

namespace App\Listing\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateListingRequest
{
    #[Assert\NotBlank(message: 'validation.listing.title.required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'validation.listing.title.min_length',
        maxMessage: 'validation.listing.title.max_length'
    )]
    public string $title;

    #[Assert\NotBlank(message: 'validation.listing.description.required')]
    #[Assert\Length(
        min: 10,
        minMessage: 'validation.listing.description.min_length'
    )]
    public string $description;

    #[Assert\NotBlank(message: 'validation.listing.price.required')]
    #[Assert\Positive(message: 'validation.listing.price.positive')]
    public float $price;

    #[Assert\Length(
        max: 10,
        maxMessage: 'validation.listing.currency.max_length'
    )]
    public ?string $currency = 'TRY';

    #[Assert\Positive(message: 'validation.listing.category_id.positive')]
    public ?int $category_id = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'validation.listing.location.max_length'
    )]
    public ?string $location = null;
}

