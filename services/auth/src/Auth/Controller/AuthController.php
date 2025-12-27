<?php

namespace App\Auth\Controller;

use App\Auth\DTO\LoginRequest;
use App\Auth\DTO\RegisterRequest;
use App\Auth\DTO\UserResponse;
use App\Auth\Service\AuthService;
use App\Auth\Service\JwtTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/auth', name: 'auth_')]
#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly JwtTokenManager $jwtTokenManager
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(summary: 'Register a new user')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['email', 'password', 'name'],
        properties: [
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'password', type: 'string', minLength: 6),
            new OA\Property(property: 'name', type: 'string')
        ]
    ))]
    #[OA\Response(response: 201, description: 'User registered successfully')]
    #[OA\Response(response: 400, description: 'Validation error')]
    public function register(
        #[MapRequestPayload] RegisterRequest $request
    ): JsonResponse {
        $user = $this->authService->register($request);
        $token = $this->jwtTokenManager->createToken($user);
        
        $response = UserResponse::fromEntity($user);

        return $this->json([
            'user' => $response,
            'token' => $token
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(summary: 'Login user')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['email', 'password'],
        properties: [
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'password', type: 'string')
        ]
    ))]
    #[OA\Response(response: 200, description: 'Login successful, returns JWT token')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    public function login(
        #[MapRequestPayload] LoginRequest $request
    ): JsonResponse {
        $user = $this->authService->login($request);
        $token = $this->jwtTokenManager->createToken($user);
        
        $response = UserResponse::fromEntity($user);

        return $this->json([
            'user' => $response,
            'token' => $token
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    #[OA\Get(summary: 'Get current user info', security: [['Bearer' => []]])]
    #[OA\Response(response: 200, description: 'Returns current user info')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function me(Request $request): JsonResponse
    {
        // Get token from Authorization header
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(
                ['error' => 'Missing or invalid Authorization header'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        try {
            $payload = $this->jwtTokenManager->decodeToken($token);
            
            return $this->json([
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'],
            ]);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Invalid token'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}

