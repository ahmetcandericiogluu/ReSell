<?php

namespace App\Service;

use App\Client\ListingClient;
use App\DTO\ConversationDetailResponse;
use App\DTO\ConversationResponse;
use App\DTO\CreateConversationRequest;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Repository\ConversationParticipantRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ConversationService
{
    public function __construct(
        private readonly ConversationRepository $conversationRepository,
        private readonly ConversationParticipantRepository $participantRepository,
        private readonly MessageRepository $messageRepository,
        private readonly ListingClient $listingClient
    ) {
    }

    /**
     * Create or get existing conversation for a listing
     */
    public function createOrGetConversation(CreateConversationRequest $request, int $buyerId): ConversationResponse
    {
        // Fetch listing from listing-service to get seller_id
        $listing = $this->listingClient->getListing($request->listing_id);
        
        if ($listing === null) {
            throw new NotFoundHttpException('Listing not found');
        }

        $sellerId = $listing['seller_id'];

        // Prevent buyer from messaging themselves
        if ($buyerId === $sellerId) {
            throw new BadRequestHttpException('Cannot start conversation with yourself');
        }

        // Check if conversation already exists
        $conversation = $this->conversationRepository->findByListingAndParticipants(
            $request->listing_id,
            $buyerId,
            $sellerId
        );

        if ($conversation === null) {
            // Create new conversation
            $conversation = new Conversation();
            $conversation->setListingId($request->listing_id);
            $conversation->setBuyerId($buyerId);
            $conversation->setSellerId($sellerId);
            $conversation->setListingTitle($listing['title']);

            $this->conversationRepository->save($conversation, true);

            // Create participant records
            $this->createParticipants($conversation);
        }

        return $this->buildConversationResponse($conversation, $buyerId);
    }

    /**
     * Get all conversations for a user
     * @return ConversationResponse[]
     */
    public function getUserConversations(int $userId): array
    {
        $conversations = $this->conversationRepository->findByUserId($userId);

        return array_map(
            fn($c) => $this->buildConversationResponse($c, $userId),
            $conversations
        );
    }

    /**
     * Get conversation details with messages (paginated)
     */
    public function getConversationDetails(
        string $conversationId,
        int $userId,
        int $page = 1,
        int $limit = 30
    ): ConversationDetailResponse {
        $conversation = $this->conversationRepository->find($conversationId);

        if ($conversation === null) {
            throw new NotFoundHttpException('Conversation not found');
        }

        // Authorization check
        if (!$conversation->isParticipant($userId)) {
            throw new AccessDeniedHttpException('You are not a participant of this conversation');
        }

        $messages = $this->messageRepository->findByConversationPaginated($conversation, $page, $limit);
        $total = $this->messageRepository->countByConversation($conversation);

        return ConversationDetailResponse::fromEntity($conversation, $messages, $page, $limit, $total);
    }

    /**
     * Get conversation entity by ID with authorization check
     */
    public function getConversation(string $conversationId, int $userId): Conversation
    {
        $conversation = $this->conversationRepository->find($conversationId);

        if ($conversation === null) {
            throw new NotFoundHttpException('Conversation not found');
        }

        if (!$conversation->isParticipant($userId)) {
            throw new AccessDeniedHttpException('You are not a participant of this conversation');
        }

        return $conversation;
    }

    /**
     * Mark conversation as read for a user
     */
    public function markAsRead(string $conversationId, int $userId): ConversationResponse
    {
        $conversation = $this->getConversation($conversationId, $userId);

        $participant = $this->participantRepository->getOrCreate($conversation, $userId);
        $latestMessage = $this->messageRepository->findLatestByConversation($conversation);

        if ($latestMessage !== null) {
            $participant->setLastReadMessage($latestMessage);
            $this->participantRepository->save($participant, true);
        }

        return $this->buildConversationResponse($conversation, $userId);
    }

    /**
     * Create participant records for both buyer and seller
     */
    private function createParticipants(Conversation $conversation): void
    {
        $buyerParticipant = new ConversationParticipant();
        $buyerParticipant->setConversation($conversation);
        $buyerParticipant->setUserId($conversation->getBuyerId());
        $this->participantRepository->save($buyerParticipant);

        $sellerParticipant = new ConversationParticipant();
        $sellerParticipant->setConversation($conversation);
        $sellerParticipant->setUserId($conversation->getSellerId());
        $this->participantRepository->save($sellerParticipant, true);
    }

    /**
     * Build ConversationResponse with unread count and last message
     */
    private function buildConversationResponse(Conversation $conversation, int $userId): ConversationResponse
    {
        $participant = $this->participantRepository->findByConversationAndUser($conversation, $userId);
        $lastReadMessageId = $participant?->getLastReadMessage()?->getId();

        $unreadCount = $this->messageRepository->countUnreadForUser(
            $conversation,
            $userId,
            $lastReadMessageId
        );

        $lastMessage = $this->messageRepository->findLatestByConversation($conversation);

        return ConversationResponse::fromEntity($conversation, $unreadCount, $lastMessage);
    }
}

