<?php

namespace App\Controller;

use App\DTO\Listing\CreateListingRequest;
use App\DTO\Listing\ListingResponse;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/listings', name: 'api_listings_')]
class ListingController extends AbstractController
{
    public function __construct(
        private readonly ListingService $listingService
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $categoryId = $request->query->get('category_id') 
            ? (int) $request->query->get('category_id') 
            : null;
        $location = $request->query->get('location');
        $search = $request->query->get('search');

        $listings = $this->listingService->getListings($status, $categoryId, $location, $search);
        
        $response = array_map(
            fn($listing) => ListingResponse::fromEntity($listing),
            $listings
        );

        return $this->json($response);
    }

    #[Route('/me', name: 'my_listings', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myListings(): JsonResponse
    {
        $user = $this->getUser();
        $listings = $this->listingService->getMyListings($user);
        
        $response = array_map(
            fn($listing) => ListingResponse::fromEntity($listing),
            $listings
        );

        return $this->json($response);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        #[MapRequestPayload] CreateListingRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        
        $listing = $this->listingService->createListing($request, $user);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response, Response::HTTP_CREATED);
    }
}

