<?php

namespace App\DTO;

use App\Entity\Conversation;
use App\Entity\Message;

class ConversationResponse
{
    public string $id;
    public int $listing_id;
    public ?string $listing_title;
    public int $buyer_id;
    public int $seller_id;
    public string $created_at;
    public string $updated_at;
    public ?MessageResponse $last_message;
    public int $unread_count;

    public static function fromEntity(
        Conversation $conversation,
        int $unreadCount = 0,
        ?Message $lastMessage = null
    ): self {
        $dto = new self();
        $dto->id = (string) $conversation->getId();
        $dto->listing_id = $conversation->getListingId();
        $dto->listing_title = $conversation->getListingTitle();
        $dto->buyer_id = $conversation->getBuyerId();
        $dto->seller_id = $conversation->getSellerId();
        $dto->created_at = $conversation->getCreatedAt()->format('c');
        $dto->updated_at = $conversation->getUpdatedAt()->format('c');
        $dto->last_message = $lastMessage ? MessageResponse::fromEntity($lastMessage) : null;
        $dto->unread_count = $unreadCount;

        return $dto;
    }
}

