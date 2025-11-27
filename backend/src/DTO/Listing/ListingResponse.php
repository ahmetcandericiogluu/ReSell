<?php

namespace App\DTO\Listing;

use App\Entity\Listing;

class ListingResponse
{
    public int $id;
    public string $title;
    public string $description;
    public string $price;
    public string $currency;
    public string $status;
    public ?int $category_id;
    public ?string $location;
    public int $seller_id;
    public string $seller_name;
    public string $created_at;
    public string $updated_at;

    public static function fromEntity(Listing $listing): self
    {
        $response = new self();
        $response->id = $listing->getId();
        $response->title = $listing->getTitle();
        $response->description = $listing->getDescription();
        $response->price = $listing->getPrice();
        $response->currency = $listing->getCurrency();
        $response->status = $listing->getStatus();
        $response->category_id = $listing->getCategoryId();
        $response->location = $listing->getLocation();
        $response->seller_id = $listing->getSeller()->getId();
        $response->seller_name = $listing->getSeller()->getName();
        $response->created_at = $listing->getCreatedAt()->format('Y-m-d H:i:s');
        $response->updated_at = $listing->getUpdatedAt()->format('Y-m-d H:i:s');

        return $response;
    }
}

