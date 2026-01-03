<?php

namespace App\Listing\Controller;

use App\Listing\DTO\CreateListingRequest;
use App\Listing\DTO\ListingResponse;
use App\Listing\DTO\UserListingResponse;
use App\Listing\Service\ListingService;
use App\Shared\Client\ListingServiceClient;
use App\User\Repository\UserRepository;
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
        private readonly ListingService $listingService,
        private readonly UserRepository $userRepository,
        private readonly ?ListingServiceClient $listingServiceClient = null
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
        
        $response = array_map(function($listing) {
            // Get images for this listing
            $images = $this->listingImageRepository->findBy(
                ['listing' => $listing],
                ['position' => 'ASC']
            );
            return ListingResponse::fromEntity($listing, $images);
        }, $listings);

        return $this->json($response);
    }

    #[Route('/me', name: 'my_listings', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myListings(): JsonResponse
    {
        $user = $this->getUser();
        $listings = $this->listingService->getMyListings($user);
        
        $response = array_map(function($listing) {
            // Get images for this listing
            $images = $this->listingImageRepository->findBy(
                ['listing' => $listing],
                ['position' => 'ASC']
            );
            return ListingResponse::fromEntity($listing, $images);
        }, $listings);

        return $this->json($response);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        
        // Get images for this listing
        $images = $this->listingImageRepository->findBy(
            ['listing' => $listing],
            ['position' => 'ASC']
        );
        
        $response = ListingResponse::fromEntity($listing, $images);

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

    #[Route('/users/{id}/listings', name: 'user_listings', methods: ['GET'])]
    public function getUserListings(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return $this->json(
                ['error' => 'Kullanıcı bulunamadı'],
                Response::HTTP_NOT_FOUND
            );
        }

        $status = $request->query->get('status', 'active');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));

        $listings = $this->listingService->getUserListings($user, $status, $page, $limit);
        $total = $this->listingService->countUserListings($user, $status);

        $items = array_map(function($listing) {
            // Images are now handled by listing-service, not fetched here
            return UserListingResponse::fromEntity($listing, null);
        }, $listings);

        return $this->json([
            'items' => $items,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }
}

