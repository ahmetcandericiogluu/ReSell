<?php

namespace App\DTO\Listing;

use App\Entity\Listing;
use App\Entity\ListingImage;

class ListingResponse
{
    public int $id;
    public int $sellerId;
    public string $title;
    public string $description;
    public string $price;
    public string $currency;
    public string $status;
    public int $categoryId;
    public string $categoryName;
    public ?string $location;
    public string $createdAt;
    public string $updatedAt;
    /** @var array<array{id: int, url: string, position: int}> */
    public array $images = [];

    public static function fromEntity(Listing $listing): self
    {
        $response = new self();
        $response->id = $listing->getId();
        $response->sellerId = $listing->getSellerId();
        $response->title = $listing->getTitle();
        $response->description = $listing->getDescription();
        $response->price = $listing->getPrice();
        $response->currency = $listing->getCurrency();
        $response->status = $listing->getStatus();
        $response->categoryId = $listing->getCategory()->getId();
        $response->categoryName = $listing->getCategory()->getName();
        $response->location = $listing->getLocation();
        $response->createdAt = $listing->getCreatedAt()->format('Y-m-d\TH:i:s\Z');
        $response->updatedAt = $listing->getUpdatedAt()->format('Y-m-d\TH:i:s\Z');
        
        $response->images = array_map(
            fn(ListingImage $image) => [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'position' => $image->getPosition(),
            ],
            $listing->getImages()->toArray()
        );

        return $response;
    }
}

