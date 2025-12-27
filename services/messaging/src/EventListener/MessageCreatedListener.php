<?php

namespace App\EventListener;

use App\Event\MessageCreatedEvent;
use App\Realtime\PusherClient;
use App\Repository\MessageRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for MessageCreatedEvent and publishes to Pusher for realtime updates
 */
class MessageCreatedListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PusherClient $pusherClient,
        private readonly MessageRepository $messageRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageCreatedEvent::NAME => 'onMessageCreated',
        ];
    }

    public function onMessageCreated(MessageCreatedEvent $event): void
    {
        if (!$this->pusherClient->isEnabled()) {
            return;
        }

        try {
            // Fetch message for full data
            $message = $this->messageRepository->find($event->messageId);
            
            if ($message === null) {
                $this->logger->warning('Message not found for realtime publish', [
                    'messageId' => (string) $event->messageId,
                ]);
                return;
            }

            $channel = 'private-conversation.' . $event->conversationId;
            
            $payload = [
                'conversation_id' => (string) $event->conversationId,
                'message' => [
                    'id' => (string) $message->getId(),
                    'sender_id' => $message->getSenderId(),
                    'content' => $message->getContent(),
                    'created_at' => $message->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ],
            ];

            $this->pusherClient->trigger($channel, 'message.created', $payload);

            $this->logger->info('Realtime message published', [
                'channel' => $channel,
                'messageId' => (string) $message->getId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to publish realtime message', [
                'error' => $e->getMessage(),
                'conversationId' => (string) $event->conversationId,
            ]);
        }
    }
}

