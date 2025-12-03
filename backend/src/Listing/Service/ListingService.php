<?php

namespace App\Listing\Service;

use App\Listing\DTO\CreateListingRequest;
use App\Listing\Entity\Listing;
use App\Listing\Repository\ListingRepository;
use App\User\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingService
{
    public function __construct(
        private readonly ListingRepository $listingRepository
    ) {
    }

    public function createListing(CreateListingRequest $request, User $seller): Listing
    {
        $listing = new Listing();
        $listing->setSeller($seller);
        $listing->setTitle($request->title);
        $listing->setDescription($request->description);
        $listing->setPrice((string) $request->price);
        
        if ($request->currency) {
            $listing->setCurrency($request->currency);
        }
        
        if ($request->category_id) {
            $listing->setCategoryId($request->category_id);
        }
        
        if ($request->location) {
            $listing->setLocation($request->location);
        }

        // Default status is 'draft' (set in entity)
        $listing->setStatus('active'); // Create as active by default

        $this->listingRepository->save($listing, true);

        return $listing;
    }

    /**
     * Get all listings with optional filters
     * @return Listing[]
     */
    public function getListings(
        ?string $status = null,
        ?int $categoryId = null,
        ?string $location = null,
        ?string $search = null
    ): array {
        return $this->listingRepository->findByFilters($status, $categoryId, $location, $search);
    }

    /**
     * Get a single listing by ID
     */
    public function getListingById(int $id): Listing
    {
        $listing = $this->listingRepository->findById($id);
        
        if (!$listing) {
            throw new NotFoundHttpException('İlan bulunamadı');
        }

        return $listing;
    }

    /**
     * Get all listings by seller
     * @return Listing[]
     */
    public function getMyListings(User $seller): array
    {
        return $this->listingRepository->findBySeller($seller->getId());
    }

    /**
     * Get listings by user with filters
     * @return Listing[]
     */
    public function getUserListings(User $user, string $status = 'active', int $page = 1, int $limit = 10): array
    {
        return $this->listingRepository->findByUserAndStatus($user, $status, $page, $limit);
    }

    /**
     * Count listings by user
     */
    public function countUserListings(User $user, string $status = 'active'): int
    {
        return $this->listingRepository->countByUserAndStatus($user, $status);
    }
}

