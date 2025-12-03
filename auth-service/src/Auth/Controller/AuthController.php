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

#[Route('/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly JwtTokenManager $jwtTokenManager
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
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

