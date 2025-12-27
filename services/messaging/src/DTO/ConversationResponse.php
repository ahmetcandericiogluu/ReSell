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
    
    // Other user info (the person you're chatting with)
    public int $other_user_id;
    public ?string $other_user_name = null;

    public static function fromEntity(
        Conversation $conversation,
        int $unreadCount = 0,
        ?Message $lastMessage = null,
        int $currentUserId = 0,
        ?string $otherUserName = null
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
        
        // Set other user info based on current user
        $dto->other_user_id = $conversation->getBuyerId() === $currentUserId
            ? $conversation->getSellerId()
            : $conversation->getBuyerId();
        $dto->other_user_name = $otherUserName;

        return $dto;
    }
}

