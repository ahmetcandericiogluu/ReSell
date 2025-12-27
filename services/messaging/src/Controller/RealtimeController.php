<?php

namespace App\Controller;

use App\Realtime\PusherClient;
use App\Repository\ConversationParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/realtime', name: 'realtime_')]
#[OA\Tag(name: 'Realtime')]
class RealtimeController extends AbstractController
{
    public function __construct(
        private readonly PusherClient $pusherClient,
        private readonly ConversationParticipantRepository $participantRepository
    ) {
    }

    /**
     * Authenticate private channel subscription
     * 
     * Pusher client calls this endpoint to authorize private channel access.
     * Only conversation participants can subscribe to private-conversation.{id} channels.
     */
    #[Route('/auth', name: 'auth', methods: ['POST'])]
    #[OA\Post(summary: 'Authenticate Pusher private channel')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['socket_id', 'channel_name'],
            properties: [
                new OA\Property(property: 'socket_id', type: 'string', example: '123.456'),
                new OA\Property(property: 'channel_name', type: 'string', example: 'private-conversation.abc123')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Auth successful')]
    #[OA\Response(response: 403, description: 'Not authorized to subscribe')]
    #[OA\Response(response: 400, description: 'Invalid channel format')]
    public function auth(Request $request): Response
    {
        // Check if Pusher is enabled
        if (!$this->pusherClient->isEnabled()) {
            return new JsonResponse(['error' => 'Realtime not enabled'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Get current user
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Parse request - handle both JSON and form-urlencoded
        $contentType = $request->headers->get('Content-Type', '');
        
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode($request->getContent(), true) ?? [];
            $socketId = $data['socket_id'] ?? null;
            $channelName = $data['channel_name'] ?? null;
        } else {
            // Pusher sends as form-urlencoded by default
            $socketId = $request->request->get('socket_id');
            $channelName = $request->request->get('channel_name');
        }

        if (empty($socketId) || empty($channelName)) {
            return new JsonResponse(['error' => 'Missing socket_id or channel_name'], Response::HTTP_BAD_REQUEST);
        }

        // Get user ID from JwtUser
        $userId = method_exists($user, 'getId') ? $user->getId() : (int) $user->getUserIdentifier();

        // Handle user channel: private-user.{userId}
        if (preg_match('/^private-user\.(\d+)$/', $channelName, $matches)) {
            $channelUserId = (int) $matches[1];
            
            // User can only subscribe to their own channel
            if ($channelUserId !== $userId) {
                return new JsonResponse(['error' => 'Not authorized for this channel'], Response::HTTP_FORBIDDEN);
            }
        }
        // Handle conversation channel: private-conversation.{uuid}
        elseif (preg_match('/^private-conversation\.([a-f0-9-]+)$/i', $channelName, $matches)) {
            $conversationId = $matches[1];

            // Verify user is participant of this conversation
            $participant = $this->participantRepository->findByConversationAndUser($conversationId, $userId);

            if (!$participant) {
                return new JsonResponse(['error' => 'Not authorized for this channel'], Response::HTTP_FORBIDDEN);
            }
        } else {
            return new JsonResponse(['error' => 'Invalid channel format'], Response::HTTP_BAD_REQUEST);
        }

        // Generate Pusher auth response
        $auth = $this->pusherClient->socketAuth($channelName, $socketId);

        if ($auth === null) {
            return new JsonResponse(['error' => 'Auth failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return raw auth response (Pusher expects specific format)
        return new Response($auth, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Get Pusher configuration for frontend
     */
    #[Route('/config', name: 'config', methods: ['GET'])]
    #[OA\Get(summary: 'Get Pusher configuration')]
    #[OA\Response(response: 200, description: 'Pusher config')]
    public function config(): JsonResponse
    {
        return $this->json([
            'enabled' => $this->pusherClient->isEnabled(),
            'key' => $this->pusherClient->getKey(),
            'cluster' => $this->pusherClient->getCluster(),
        ]);
    }
}

