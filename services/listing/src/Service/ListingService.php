<?php

namespace App\Service;

use App\DTO\Listing\ListingCreateRequest;
use App\DTO\Listing\ListingUpdateRequest;
use App\Entity\Listing;
use App\Entity\ListingImage;
use App\Repository\CategoryRepository;
use App\Repository\ListingRepository;
use App\Repository\ListingImageRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ListingService
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ListingImageRepository $imageRepository,
    ) {
    }

    public function createListing(ListingCreateRequest $request, int $sellerId): Listing
    {
        $category = $this->categoryRepository->find($request->categoryId);
        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        $listing = new Listing();
        $listing->setSellerId($sellerId);
        $listing->setTitle($request->title);
        $listing->setDescription($request->description);
        $listing->setPrice((string) $request->price);
        $listing->setCurrency($request->currency);
        $listing->setCategory($category);
        $listing->setLocation($request->location);
        $listing->setStatus($request->status);

        // Add images if provided
        if (!empty($request->imageUrls)) {
            foreach ($request->imageUrls as $index => $url) {
                $image = new ListingImage();
                $image->setUrl($url);
                $image->setPosition($index);
                $listing->addImage($image);
            }
        }

        $this->listingRepository->save($listing, true);

        return $listing;
    }

    public function getListingById(int $id): Listing
    {
        $listing = $this->listingRepository->findByIdAndNotDeleted($id);
        if (!$listing) {
            throw new NotFoundHttpException('Listing not found');
        }

        return $listing;
    }

    public function updateListing(int $id, ListingUpdateRequest $request, int $userId): Listing
    {
        $listing = $this->getListingById($id);

        // Check if user is the seller
        if ($listing->getSellerId() !== $userId) {
            throw new AccessDeniedHttpException('You are not authorized to update this listing');
        }

        // Update fields if provided
        if ($request->title !== null) {
            $listing->setTitle($request->title);
        }

        if ($request->description !== null) {
            $listing->setDescription($request->description);
        }

        if ($request->price !== null) {
            $listing->setPrice((string) $request->price);
        }

        if ($request->currency !== null) {
            $listing->setCurrency($request->currency);
        }

        if ($request->categoryId !== null) {
            $category = $this->categoryRepository->find($request->categoryId);
            if (!$category) {
                throw new NotFoundHttpException('Category not found');
            }
            $listing->setCategory($category);
        }

        if ($request->location !== null) {
            $listing->setLocation($request->location);
        }

        if ($request->status !== null) {
            $listing->setStatus($request->status);
        }

        $this->listingRepository->save($listing, true);

        return $listing;
    }

    public function deleteListing(int $id, int $userId): void
    {
        $listing = $this->getListingById($id);

        // Check if user is the seller
        if ($listing->getSellerId() !== $userId) {
            throw new AccessDeniedHttpException('You are not authorized to delete this listing');
        }

        $listing->softDelete();
        $this->listingRepository->save($listing, true);
    }

    public function getListings(
        ?string $status = 'active',
        ?int $categoryId = null,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?string $location = null,
        int $page = 1,
        int $limit = 20
    ): array {
        return $this->listingRepository->findWithFilters(
            $status,
            $categoryId,
            $priceMin,
            $priceMax,
            $location,
            $page,
            $limit
        );
    }

    public function countListings(
        ?string $status = 'active',
        ?int $categoryId = null,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?string $location = null
    ): int {
        return $this->listingRepository->countWithFilters(
            $status,
            $categoryId,
            $priceMin,
            $priceMax,
            $location
        );
    }

    public function getListingsByUserId(int $userId): array
    {
        return $this->listingRepository->findBySellerIdAndNotDeleted($userId);
    }
}

