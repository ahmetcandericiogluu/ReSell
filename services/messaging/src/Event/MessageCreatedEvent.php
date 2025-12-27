<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Uid\Uuid;

/**
 * Event dispatched when a new message is created.
 * Designed for future realtime integration (websockets, Mercure, etc.)
 */
class MessageCreatedEvent extends Event
{
    public const NAME = 'message.created';

    public function __construct(
        public readonly Uuid $conversationId,
        public readonly Uuid $messageId,
        public readonly int $senderId,
        public readonly int $recipientId
    ) {
    }
}

