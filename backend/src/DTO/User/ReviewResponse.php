<?php

namespace App\DTO\User;

use App\Entity\Review;

class ReviewResponse
{
    public int $id;
    public int $rating;
    public ?string $comment;
    public string $createdAt;
    public array $buyer;
    public array $listing;

    public static function fromEntity(Review $review): self
    {
        $dto = new self();
        $dto->id = $review->getId();
        $dto->rating = $review->getRating();
        $dto->comment = $review->getComment();
        $dto->createdAt = $review->getCreatedAt()->format('Y-m-d\TH:i:s\Z');
        $dto->buyer = [
            'id' => $review->getBuyer()->getId(),
            'name' => $review->getBuyer()->getName(),
        ];
        $dto->listing = [
            'id' => $review->getListing()->getId(),
            'title' => $review->getListing()->getTitle(),
        ];

        return $dto;
    }
}

