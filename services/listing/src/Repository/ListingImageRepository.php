<?php

namespace App\Repository;

use App\Entity\ListingImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListingImage>
 */
class ListingImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListingImage::class);
    }

    public function save(ListingImage $image, bool $flush = false): void
    {
        $this->getEntityManager()->persist($image);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ListingImage $image, bool $flush = false): void
    {
        $this->getEntityManager()->remove($image);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

