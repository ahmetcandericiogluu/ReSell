<?php

namespace App\Controller;

use App\Repository\ListingRepository;
use App\Repository\ListingImageRepository;
use App\Service\ListingImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/listings')]
class ImageController extends AbstractController
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly ListingImageRepository $imageRepository,
        private readonly ListingImageService $imageService
    ) {
    }

    #[Route('/{id}/images', name: 'upload_images', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadImages(int $id, Request $request): JsonResponse
    {
        $listing = $this->listingRepository->find($id);
        
        if (!$listing) {
            return $this->json(
                ['error' => 'Listing not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Check if user is the owner
        $userId = $request->attributes->get('user_id');
        if ((int)$listing->getSellerId() !== (int)$userId) {
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
            $images = $this->imageService->addImages($listing, $files);
            
            // Format response
            $response = array_map(function ($image) {
                return [
                    'id' => $image->getId(),
                    'url' => $image->getUrl(),
                    'position' => $image->getPosition(),
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
        $listing = $this->listingRepository->find($id);
        
        if (!$listing) {
            return $this->json(
                ['error' => 'Listing not found'],
                Response::HTTP_NOT_FOUND
            );
        }
        
        $images = $this->imageRepository->findBy(
            ['listing' => $listing],
            ['position' => 'ASC']
        );

        $response = array_map(function ($image) {
            return [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'position' => $image->getPosition(),
            ];
        }, $images);

        return $this->json($response);
    }

    #[Route('/{listingId}/images/{imageId}', name: 'delete_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteImage(int $listingId, int $imageId, Request $request): JsonResponse
    {
        $listing = $this->listingRepository->find($listingId);
        
        if (!$listing) {
            return $this->json(
                ['error' => 'Listing not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Check if user is the owner
        $userId = $request->attributes->get('user_id');
        if ((int)$listing->getSellerId() !== (int)$userId) {
            return $this->json(
                ['error' => 'You are not authorized to delete images for this listing'],
                Response::HTTP_FORBIDDEN
            );
        }

        $image = $this->imageRepository->find($imageId);
        
        if (!$image || $image->getListing()->getId() !== $listingId) {
            return $this->json(
                ['error' => 'Image not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $this->imageService->deleteImage($image);

            return $this->json(
                ['message' => 'Image deleted successfully'],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Failed to delete image: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

