<?php

namespace App\Controller;

use App\DTO\Listing\ListingCreateRequest;
use App\DTO\Listing\ListingResponse;
use App\DTO\Listing\ListingUpdateRequest;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/listings')]
class ListingController extends AbstractController
{
    public function __construct(
        private readonly ListingService $listingService
    ) {
    }

    #[Route('/me', name: 'listings_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyListings(Request $httpRequest): JsonResponse
    {
        $userId = $httpRequest->attributes->get('user_id');
        
        $listings = $this->listingService->getListingsByUserId((int) $userId);
        
        $response = array_map(
            fn($listing) => ListingResponse::fromEntity($listing),
            $listings
        );

        return $this->json($response);
    }

    #[Route('', name: 'listings_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status', 'active');
        $categoryId = $request->query->get('category_id') 
            ? (int) $request->query->get('category_id') 
            : null;
        $priceMin = $request->query->get('price_min') 
            ? (float) $request->query->get('price_min') 
            : null;
        $priceMax = $request->query->get('price_max') 
            ? (float) $request->query->get('price_max') 
            : null;
        $location = $request->query->get('location');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $listings = $this->listingService->getListings(
            $status,
            $categoryId,
            $priceMin,
            $priceMax,
            $location,
            $page,
            $limit
        );

        $total = $this->listingService->countListings(
            $status,
            $categoryId,
            $priceMin,
            $priceMax,
            $location
        );

        $response = array_map(
            fn($listing) => ListingResponse::fromEntity($listing),
            $listings
        );

        return $this->json([
            'data' => $response,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit),
            ]
        ]);
    }

    #[Route('/{id}', name: 'listings_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response);
    }

    #[Route('', name: 'listings_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        #[MapRequestPayload] ListingCreateRequest $request,
        Request $httpRequest
    ): JsonResponse {
        $userId = $httpRequest->attributes->get('user_id');
        
        $listing = $this->listingService->createListing($request, (int) $userId);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'listings_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(
        int $id,
        #[MapRequestPayload] ListingUpdateRequest $request,
        Request $httpRequest
    ): JsonResponse {
        $userId = $httpRequest->attributes->get('user_id');
        
        $listing = $this->listingService->updateListing($id, $request, (int) $userId);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response);
    }

    #[Route('/{id}', name: 'listings_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id, Request $httpRequest): JsonResponse
    {
        $userId = $httpRequest->attributes->get('user_id');
        
        $this->listingService->deleteListing($id, (int) $userId);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

