<?php

namespace App\User\Controller;

use App\User\DTO\UpdateProfileRequest;
use App\User\DTO\UserProfileResponse;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/me', name: 'user_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(
                ['error' => 'Giriş yapılmamış'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $response = UserProfileResponse::fromEntity($user);

        return $this->json($response);
    }

    #[Route('/users/{id}', name: 'user_profile', methods: ['GET'])]
    public function profile(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return $this->json(
                ['error' => 'Kullanıcı bulunamadı'],
                Response::HTTP_NOT_FOUND
            );
        }

        $response = UserProfileResponse::fromEntity($user);

        return $this->json($response);
    }

    #[Route('/me', name: 'user_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(
        #[MapRequestPayload] UpdateProfileRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(
                ['error' => 'Giriş yapılmamış'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Update user directly
        if ($request->name !== null) {
            $user->setName($request->name);
        }

        if ($request->city !== null) {
            $user->setCity($request->city);
        }

        if ($request->phone !== null) {
            $user->setPhone($request->phone);
        }

        $this->userRepository->save($user, true);
        $response = UserProfileResponse::fromEntity($user);

        return $this->json($response);
    }
}

