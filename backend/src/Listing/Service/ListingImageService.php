<?php

namespace App\Listing\Service;

use App\Listing\Entity\Listing;
use App\Listing\Entity\ListingImage;
use App\Shared\Storage\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ListingImageService
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EntityManagerInterface $em,
        private readonly string $storageDriver = 'local'
    ) {
    }

    /**
     * Add images to a listing
     * 
     * @param Listing $listing
     * @param array $files Array of UploadedFile instances
     * @return array Array of created ListingImage entities
     * @throws \InvalidArgumentException
     */
    public function addImages(Listing $listing, array $files): array
    {
        $images = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            // Validate file
            $this->validateFile($file);

            // Upload file to storage
            $data = $this->storage->upload($file, 'listings/' . $listing->getId());

            // Create ListingImage entity
            $image = new ListingImage();
            $image->setListing($listing);
            $image->setStorageDriver($this->storageDriver);
            $image->setPath($data['path']);
            $image->setUrl($data['url']);
            
            // Set position (last position + 1)
            $lastPosition = $this->getLastPosition($listing);
            $image->setPosition($lastPosition + 1);

            $this->em->persist($image);
            $images[] = $image;
        }

        $this->em->flush();

        return $images;
    }

    /**
     * Delete an image
     * 
     * @param ListingImage $image
     * @return void
     */
    public function deleteImage(ListingImage $image): void
    {
        // Delete from storage
        $this->storage->delete($image->getPath());

        // Delete from database
        $this->em->remove($image);
        $this->em->flush();
    }

    /**
     * Validate uploaded file
     * 
     * @param UploadedFile $file
     * @throws \InvalidArgumentException
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                sprintf('File size exceeds maximum allowed size of %d bytes', self::MAX_FILE_SIZE)
            );
        }

        // Check mime type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid file type. Allowed types: %s',
                    implode(', ', self::ALLOWED_MIME_TYPES)
                )
            );
        }
    }

    /**
     * Get the last position for images in a listing
     * 
     * @param Listing $listing
     * @return int
     */
    private function getLastPosition(Listing $listing): int
    {
        $result = $this->em->getRepository(ListingImage::class)
            ->createQueryBuilder('li')
            ->select('MAX(li.position)')
            ->where('li.listing = :listing')
            ->setParameter('listing', $listing)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}

