<?php

namespace App\Service;

use App\DTO\CreateMessageRequest;
use App\DTO\MessageResponse;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Event\MessageCreatedEvent;
use App\Repository\ConversationParticipantRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly ConversationParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Create a new message in a conversation
     */
    public function createMessage(
        Conversation $conversation,
        CreateMessageRequest $request,
        int $senderId
    ): MessageResponse {
        // Authorization check
        if (!$conversation->isParticipant($senderId)) {
            throw new AccessDeniedHttpException('You are not a participant of this conversation');
        }

        // Create message
        $message = new Message();
        $message->setConversation($conversation);
        $message->setSenderId($senderId);
        $message->setContent($request->content);

        $this->messageRepository->save($message);

        // Update conversation timestamp
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        // Auto-mark as read for sender
        $senderParticipant = $this->participantRepository->getOrCreate($conversation, $senderId);
        $senderParticipant->setLastReadMessage($message);
        $this->participantRepository->save($senderParticipant, true);

        // Dispatch event for future realtime integration
        $recipientId = $conversation->getBuyerId() === $senderId
            ? $conversation->getSellerId()
            : $conversation->getBuyerId();

        $this->eventDispatcher->dispatch(
            new MessageCreatedEvent(
                $conversation->getId(),
                $message->getId(),
                $senderId,
                $recipientId
            ),
            MessageCreatedEvent::NAME
        );

        return MessageResponse::fromEntity($message);
    }
}

