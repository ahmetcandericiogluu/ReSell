<?php

namespace App\DTO;

use App\Entity\Message;

class MessageResponse
{
    public string $id;
    public string $conversation_id;
    public int $sender_id;
    public string $content;
    public string $created_at;

    public static function fromEntity(Message $message): self
    {
        $dto = new self();
        $dto->id = (string) $message->getId();
        $dto->conversation_id = (string) $message->getConversation()->getId();
        $dto->sender_id = $message->getSenderId();
        $dto->content = $message->getContent();
        $dto->created_at = $message->getCreatedAt()->format('c');

        return $dto;
    }
}

