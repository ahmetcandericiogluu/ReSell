<?php

namespace App\DTO;

use App\Entity\Conversation;

class ConversationDetailResponse
{
    public string $id;
    public int $listing_id;
    public ?string $listing_title;
    public int $buyer_id;
    public int $seller_id;
    public string $created_at;
    public string $updated_at;
    /** @var MessageResponse[] */
    public array $messages;
    public array $meta;

    public static function fromEntity(
        Conversation $conversation,
        array $messages,
        int $page,
        int $limit,
        int $total
    ): self {
        $dto = new self();
        $dto->id = (string) $conversation->getId();
        $dto->listing_id = $conversation->getListingId();
        $dto->listing_title = $conversation->getListingTitle();
        $dto->buyer_id = $conversation->getBuyerId();
        $dto->seller_id = $conversation->getSellerId();
        $dto->created_at = $conversation->getCreatedAt()->format('c');
        $dto->updated_at = $conversation->getUpdatedAt()->format('c');
        $dto->messages = array_map(
            fn($m) => MessageResponse::fromEntity($m),
            $messages
        );
        $dto->meta = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => (int) ceil($total / $limit),
        ];

        return $dto;
    }
}

