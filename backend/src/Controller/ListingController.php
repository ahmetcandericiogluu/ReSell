<?php

namespace App\Controller;

use App\DTO\Listing\CreateListingRequest;
use App\DTO\Listing\ListingResponse;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/listings', name: 'api_listings_')]
#[IsGranted('ROLE_USER')]
class ListingController extends AbstractController
{
    public function __construct(
        private readonly ListingService $listingService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateListingRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        
        $listing = $this->listingService->createListing($request, $user);
        $response = ListingResponse::fromEntity($listing);

        return $this->json($response, Response::HTTP_CREATED);
    }
}

