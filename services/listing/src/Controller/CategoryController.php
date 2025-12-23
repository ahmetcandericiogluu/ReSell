<?php

namespace App\Controller;

use App\DTO\Listing\CategoryResponse;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/categories')]
#[OA\Tag(name: 'Categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    #[Route('', name: 'categories_index', methods: ['GET'])]
    #[OA\Get(summary: 'Get all categories')]
    #[OA\Response(response: 200, description: 'Returns all categories')]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAllActive();
        
        $response = array_map(
            fn($category) => CategoryResponse::fromEntity($category),
            $categories
        );

        return $this->json($response);
    }
}

