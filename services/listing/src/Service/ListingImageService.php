<?php

namespace App\Service;

use App\Entity\Listing;
use App\Entity\ListingImage;
use App\Repository\ListingImageRepository;
use App\Storage\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ListingImageService
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly ListingImageRepository $imageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Add images to a listing
     * @param Listing $listing
     * @param UploadedFile[] $files
     * @return ListingImage[]
     */
    public function addImages(Listing $listing, array $files): array
    {
        // Validate file types
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        foreach ($files as $file) {
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                throw new \InvalidArgumentException('Only JPEG, PNG and WebP images are allowed');
            }
        }

        // Get current max position
        $maxPosition = $this->imageRepository->findMaxPosition($listing) ?? 0;

        $images = [];
        foreach ($files as $file) {
            // Upload to storage
            $uploadResult = $this->storage->upload($file, 'listings/' . $listing->getId());

            // Create image entity
            $image = new ListingImage();
            $image->setListing($listing);
            $image->setUrl($uploadResult['url']);
            $image->setPosition(++$maxPosition);

            $this->entityManager->persist($image);
            $images[] = $image;
        }

        $this->entityManager->flush();

        return $images;
    }

    /**
     * Delete an image
     * @param ListingImage $image
     * @return void
     */
    public function deleteImage(ListingImage $image): void
    {
        // Extract path from URL (format: https://domain.com/listings/123/filename.jpg)
        $url = $image->getUrl();
        $path = parse_url($url, PHP_URL_PATH);
        
        // Remove leading slash
        if ($path && str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        // Delete from storage
        if ($path) {
            try {
                $this->storage->delete($path);
            } catch (\Exception $e) {
                // Log but don't fail - storage might be already deleted
            }
        }

        // Delete from database
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
}

