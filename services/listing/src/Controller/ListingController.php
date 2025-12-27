<?php

namespace App\Controller;

use App\DTO\Listing\ListingCreateRequest;
use App\DTO\Listing\ListingResponse;
use App\DTO\Listing\ListingUpdateRequest;
use App\Elasticsearch\ListingSearchService;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/listings')]
#[OA\Tag(name: 'Listings')]
class ListingController extends AbstractController
{
    public function __construct(
        private readonly ListingService $listingService,
        private readonly ListingSearchService $searchService
    ) {
    }

    #[Route('/search', name: 'listings_search', methods: ['GET'])]
    #[OA\Get(summary: 'Search listings using Elasticsearch')]
    #[OA\Parameter(name: 'q', in: 'query', description: 'Full-text search query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'categoryId', in: 'query', description: 'Filter by category ID', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'minPrice', in: 'query', description: 'Minimum price', schema: new OA\Schema(type: 'number'))]
    #[OA\Parameter(name: 'maxPrice', in: 'query', description: 'Maximum price', schema: new OA\Schema(type: 'number'))]
    #[OA\Parameter(name: 'location', in: 'query', description: 'Location filter', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'sort', in: 'query', description: 'Sort by (created_at, price)', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'order', in: 'query', description: 'Sort order (asc, desc)', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'limit', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Search results from Elasticsearch')]
    public function search(Request $request): JsonResponse
    {
        $results = $this->searchService->search(
            query: $request->query->get('q'),
            categoryId: $request->query->get('categoryId') ? (int) $request->query->get('categoryId') : null,
            minPrice: $request->query->get('minPrice') ? (float) $request->query->get('minPrice') : null,
            maxPrice: $request->query->get('maxPrice') ? (float) $request->query->get('maxPrice') : null,
            location: $request->query->get('location'),
            sort: $request->query->get('sort', 'created_at'),
            order: $request->query->get('order', 'desc'),
            page: max(1, (int) $request->query->get('page', 1)),
            limit: min(100, max(1, (int) $request->query->get('limit', 20)))
        );

        return $this->json($results);
    }

    #[Route('/my-listings', name: 'listings_my', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Get current user listings', security: [['Bearer' => []]])]
    #[OA\Response(response: 200, description: 'Returns user listings')]
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
    #[OA\Get(summary: 'Get all listings')]
    #[OA\Parameter(name: 'status', in: 'query', description: 'Filter by status', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'category_id', in: 'query', description: 'Filter by category', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'limit', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Returns paginated listings')]
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
    #[OA\Get(summary: 'Get single listing by ID')]
    #[OA\Response(response: 200, description: 'Returns listing details')]
    #[OA\Response(response: 404, description: 'Listing not found')]
    public function show(int $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response);
    }

    #[Route('', name: 'listings_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Create a new listing', security: [['Bearer' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ListingCreateRequest'))]
    #[OA\Response(response: 201, description: 'Listing created')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
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
    #[OA\Put(summary: 'Update a listing', security: [['Bearer' => []]])]
    #[OA\Response(response: 200, description: 'Listing updated')]
    #[OA\Response(response: 403, description: 'Not owner')]
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
    #[OA\Delete(summary: 'Delete a listing', security: [['Bearer' => []]])]
    #[OA\Response(response: 204, description: 'Listing deleted')]
    #[OA\Response(response: 403, description: 'Not owner')]
    public function delete(int $id, Request $httpRequest): JsonResponse
    {
        $userId = $httpRequest->attributes->get('user_id');
        
        $this->listingService->deleteListing($id, (int) $userId);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/refresh-index', name: 'listings_refresh_index', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Refresh listing in Elasticsearch index')]
    #[OA\Response(response: 200, description: 'Index refreshed')]
    #[OA\Response(response: 404, description: 'Listing not found')]
    public function refreshIndex(int $id): JsonResponse
    {
        $this->listingService->refreshIndex($id);

        return $this->json(['success' => true, 'message' => 'Index refreshed']);
    }
}

