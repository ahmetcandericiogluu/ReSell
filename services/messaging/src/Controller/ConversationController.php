<?php

namespace App\Controller;

use App\DTO\CreateConversationRequest;
use App\DTO\CreateMessageRequest;
use App\DTO\MarkReadRequest;
use App\Realtime\PusherClient;
use App\Service\ConversationService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/conversations')]
#[OA\Tag(name: 'Conversations')]
class ConversationController extends AbstractController
{
    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly MessageService $messageService,
        private readonly PusherClient $pusherClient
    ) {
    }

    /**
     * Create or get existing conversation for a listing
     */
    #[Route('', name: 'conversations_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Create or get conversation for a listing', security: [['Bearer' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'listing_id', type: 'integer', example: 123)
        ]
    ))]
    #[OA\Response(response: 200, description: 'Conversation created or retrieved')]
    #[OA\Response(response: 400, description: 'Cannot message yourself')]
    #[OA\Response(response: 404, description: 'Listing not found')]
    public function create(
        #[MapRequestPayload] CreateConversationRequest $request,
        Request $httpRequest
    ): JsonResponse {
        $userId = (int) $httpRequest->attributes->get('user_id');
        
        $response = $this->conversationService->createOrGetConversation($request, $userId);

        return $this->json($response, Response::HTTP_OK);
    }

    /**
     * Get all conversations for current user
     */
    #[Route('', name: 'conversations_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Get all conversations for current user', security: [['Bearer' => []]])]
    #[OA\Response(response: 200, description: 'List of conversations')]
    public function list(Request $httpRequest): JsonResponse
    {
        $userId = (int) $httpRequest->attributes->get('user_id');
        
        $conversations = $this->conversationService->getUserConversations($userId);

        return $this->json($conversations);
    }

    /**
     * Get conversation details with messages (paginated)
     */
    #[Route('/{id}', name: 'conversations_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Get conversation details with messages', security: [['Bearer' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 30))]
    #[OA\Response(response: 200, description: 'Conversation details with messages')]
    #[OA\Response(response: 403, description: 'Not a participant')]
    #[OA\Response(response: 404, description: 'Conversation not found')]
    public function show(string $id, Request $httpRequest): JsonResponse
    {
        $userId = (int) $httpRequest->attributes->get('user_id');
        $page = max(1, (int) $httpRequest->query->get('page', 1));
        $limit = min(100, max(1, (int) $httpRequest->query->get('limit', 30)));

        $response = $this->conversationService->getConversationDetails($id, $userId, $page, $limit);

        return $this->json($response);
    }

    /**
     * Post a message to a conversation
     */
    #[Route('/{id}/messages', name: 'conversations_post_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Post a message to conversation', security: [['Bearer' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'content', type: 'string', example: 'Hello, is this still available?')
        ]
    ))]
    #[OA\Response(response: 201, description: 'Message created')]
    #[OA\Response(response: 403, description: 'Not a participant')]
    #[OA\Response(response: 404, description: 'Conversation not found')]
    public function postMessage(
        string $id,
        #[MapRequestPayload] CreateMessageRequest $request,
        Request $httpRequest
    ): JsonResponse {
        $userId = (int) $httpRequest->attributes->get('user_id');

        $conversation = $this->conversationService->getConversation($id, $userId);
        $response = $this->messageService->createMessage($conversation, $request, $userId);

        return $this->json($response, Response::HTTP_CREATED);
    }

    /**
     * Mark conversation as read
     */
    #[Route('/{id}/read', name: 'conversations_mark_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Mark conversation as read', security: [['Bearer' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Conversation marked as read')]
    #[OA\Response(response: 403, description: 'Not a participant')]
    #[OA\Response(response: 404, description: 'Conversation not found')]
    public function markRead(string $id, Request $httpRequest): JsonResponse
    {
        $userId = (int) $httpRequest->attributes->get('user_id');

        $response = $this->conversationService->markAsRead($id, $userId);

        return $this->json($response);
    }

    /**
     * Send typing indicator to conversation
     */
    #[Route('/{id}/typing', name: 'conversations_typing', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Send typing indicator', security: [['Bearer' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Typing indicator sent')]
    #[OA\Response(response: 403, description: 'Not a participant')]
    #[OA\Response(response: 404, description: 'Conversation not found')]
    public function typing(string $id, Request $httpRequest): JsonResponse
    {
        $userId = (int) $httpRequest->attributes->get('user_id');

        // Verify user is participant
        $this->conversationService->getConversation($id, $userId);

        // Broadcast typing event via Pusher
        $this->pusherClient->trigger(
            "private-conversation.{$id}",
            'user.typing',
            ['user_id' => $userId]
        );

        return $this->json(['success' => true]);
    }
}

