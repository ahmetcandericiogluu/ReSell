<?php

namespace App\Controller;

use App\DTO\User\ReviewResponse;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/{id}/reviews', name: 'reviews', methods: ['GET'])]
    public function getUserReviews(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return $this->json(
                ['error' => 'Kullanıcı bulunamadı'],
                Response::HTTP_NOT_FOUND
            );
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));

        $reviews = $this->reviewRepository->findByUser($user, $page, $limit);
        $total = $this->reviewRepository->countByUser($user);

        $items = array_map(
            fn($review) => ReviewResponse::fromEntity($review),
            $reviews
        );

        return $this->json([
            'items' => $items,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }
}

