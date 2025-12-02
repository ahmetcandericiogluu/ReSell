<?php

namespace App\DTO\User;

use App\Entity\Listing;
use App\Entity\ListingImage;

class UserListingResponse
{
    public int $id;
    public string $title;
    public float $price;
    public string $currency;
    public string $status;
    public ?string $thumbnailUrl;
    public string $createdAt;

    public static function fromEntity(Listing $listing, ?ListingImage $thumbnail = null): self
    {
        $dto = new self();
        $dto->id = $listing->getId();
        $dto->title = $listing->getTitle();
        $dto->price = (float) $listing->getPrice();
        $dto->currency = $listing->getCurrency();
        $dto->status = $listing->getStatus();
        $dto->thumbnailUrl = $thumbnail ? $thumbnail->getUrl() : null;
        $dto->createdAt = $listing->getCreatedAt()->format('Y-m-d\TH:i:s\Z');

        return $dto;
    }
}

