<?php

namespace App\Repository;

use App\Entity\Listing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Listing>
 */
class ListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    public function save(Listing $listing, bool $flush = false): void
    {
        $this->getEntityManager()->persist($listing);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Find all active listings
     * @return Listing[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find listings by filters
     * @return Listing[]
     */
    public function findByFilters(
        ?string $status = null,
        ?int $categoryId = null,
        ?string $location = null,
        ?string $search = null
    ): array {
        $qb = $this->createQueryBuilder('l');

        if ($status) {
            $qb->andWhere('l.status = :status')
               ->setParameter('status', $status);
        } else {
            // Default to active if no status specified
            $qb->andWhere('l.status = :status')
               ->setParameter('status', 'active');
        }

        if ($categoryId) {
            $qb->andWhere('l.categoryId = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($location) {
            $qb->andWhere('l.location LIKE :location')
               ->setParameter('location', '%' . $location . '%');
        }

        if ($search) {
            $qb->andWhere('(l.title LIKE :search OR l.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('l.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function findById(int $id): ?Listing
    {
        return $this->find($id);
    }

    /**
     * Find all listings by seller
     * @return Listing[]
     */
    public function findBySeller(int $sellerId): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.seller = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find listings by user and status with pagination
     * @return Listing[]
     */
    public function findByUserAndStatus($user, string $status, int $page, int $limit): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.seller = :user')
            ->andWhere('l.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('l.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count listings by user and status
     */
    public function countByUserAndStatus($user, string $status): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.seller = :user')
            ->andWhere('l.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

