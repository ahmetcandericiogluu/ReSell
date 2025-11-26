<?php

namespace App\Controller;

use App\DTO\Auth\LoginRequest;
use App\DTO\Auth\RegisterRequest;
use App\DTO\Auth\UserResponse;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly Security $security,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterRequest $request
    ): JsonResponse {
        $user = $this->userService->register($request);
        $response = UserResponse::fromEntity($user);

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginRequest $request,
        Request $httpRequest
    ): JsonResponse {
        $user = $this->userService->login($request);
        
        // Create authentication token and store in session
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        
        // Save user identifier in session for authenticator
        $session = $httpRequest->getSession();
        $session->set('_security_main', serialize($token));
        $session->set('_security_user_identifier', $user->getUserIdentifier());
        
        $response = UserResponse::fromEntity($user);

        return $this->json($response);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        // Clear token
        $this->tokenStorage->setToken(null);
        
        // Clear session
        $session = $request->getSession();
        $session->invalidate();

        return $this->json(['message' => 'Başarıyla çıkış yapıldı']);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(
                ['error' => 'Giriş yapılmamış'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $response = UserResponse::fromEntity($user);

        return $this->json($response);
    }
}

