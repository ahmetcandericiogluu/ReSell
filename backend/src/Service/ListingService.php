<?php

namespace App\Service;

use App\DTO\Listing\CreateListingRequest;
use App\Entity\Listing;
use App\Entity\User;
use App\Repository\ListingRepository;

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
}

