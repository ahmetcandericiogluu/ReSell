<?php

namespace App\Controller;

use App\DTO\Listing\CreateListingRequest;
use App\DTO\Listing\ListingResponse;
use App\Entity\ListingImage;
use App\Repository\ListingImageRepository;
use App\Service\ListingImageService;
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
        private readonly ListingService $listingService,
        private readonly ListingImageService $listingImageService,
        private readonly ListingImageRepository $listingImageRepository
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

    #[Route('/{id}/images', name: 'upload_images', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadImages(int $id, Request $request): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        
        // Check if user is the owner
        if ($listing->getSeller()->getId() !== $this->getUser()->getId()) {
            return $this->json(
                ['error' => 'You are not authorized to upload images for this listing'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Get uploaded files
        $files = $request->files->all('images');
        
        if (empty($files)) {
            return $this->json(
                ['error' => 'No images provided'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $images = $this->listingImageService->addImages($listing, $files);
            
            // Format response
            $response = array_map(function (ListingImage $image) {
                return [
                    'id' => $image->getId(),
                    'url' => $image->getUrl(),
                    'path' => $image->getPath(),
                    'position' => $image->getPosition(),
                    'storage_driver' => $image->getStorageDriver(),
                    'created_at' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }, $images);

            return $this->json($response, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Failed to upload images: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{id}/images', name: 'get_images', methods: ['GET'])]
    public function getImages(int $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);
        
        $images = $this->listingImageRepository->findBy(
            ['listing' => $listing],
            ['position' => 'ASC']
        );

        $response = array_map(function (ListingImage $image) {
            return [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'path' => $image->getPath(),
                'position' => $image->getPosition(),
                'storage_driver' => $image->getStorageDriver(),
                'created_at' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $images);

        return $this->json($response);
    }

    #[Route('/{listingId}/images/{imageId}', name: 'delete_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteImage(int $listingId, int $imageId): JsonResponse
    {
        $listing = $this->listingService->getListingById($listingId);
        
        // Check if user is the owner
        if ($listing->getSeller()->getId() !== $this->getUser()->getId()) {
            return $this->json(
                ['error' => 'You are not authorized to delete images for this listing'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Find image
        $image = $this->listingImageRepository->find($imageId);
        
        if (!$image) {
            return $this->json(
                ['error' => 'Image not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Verify image belongs to listing
        if ($image->getListing()->getId() !== $listingId) {
            return $this->json(
                ['error' => 'Image does not belong to this listing'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->listingImageService->deleteImage($image);
            
            return $this->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Failed to delete image: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

