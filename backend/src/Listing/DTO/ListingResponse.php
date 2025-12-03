<?php

namespace App\Listing\DTO;

use App\Listing\Entity\Listing;
use App\Listing\Entity\ListingImage;

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
    public array $images = [];

    public static function fromEntity(Listing $listing, array $images = []): self
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
        
        // Format images
        $response->images = array_map(function (ListingImage $image) {
            return [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'path' => $image->getPath(),
                'position' => $image->getPosition(),
                'storage_driver' => $image->getStorageDriver(),
                'created_at' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $images);

        return $response;
    }
}

