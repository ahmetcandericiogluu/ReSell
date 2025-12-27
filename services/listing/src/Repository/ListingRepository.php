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

    public function remove(Listing $listing, bool $flush = false): void
    {
        $this->getEntityManager()->remove($listing);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithFilters(
        ?string $status = null,
        ?int $categoryId = null,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?string $location = null,
        int $page = 1,
        int $limit = 20
    ): array {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.category', 'c')
            ->andWhere('l.deletedAt IS NULL');

        if ($status) {
            $qb->andWhere('l.status = :status')
                ->setParameter('status', $status);
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($priceMin !== null) {
            $qb->andWhere('l.price >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $qb->andWhere('l.price <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }

        if ($location) {
            $qb->andWhere('l.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        $qb->orderBy('l.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(
        ?string $status = null,
        ?int $categoryId = null,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?string $location = null
    ): int {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->leftJoin('l.category', 'c')
            ->andWhere('l.deletedAt IS NULL');

        if ($status) {
            $qb->andWhere('l.status = :status')
                ->setParameter('status', $status);
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($priceMin !== null) {
            $qb->andWhere('l.price >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $qb->andWhere('l.price <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }

        if ($location) {
            $qb->andWhere('l.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByIdAndNotDeleted(int $id): ?Listing
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id = :id')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Listing[]
     */
    public function findBySellerIdAndNotDeleted(int $sellerId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.sellerId = :sellerId')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('sellerId', $sellerId)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get only active listings by seller (for public profile view)
     * @return Listing[]
     */
    public function findActiveBySellerIdAndNotDeleted(int $sellerId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.sellerId = :sellerId')
            ->andWhere('l.status = :status')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('sellerId', $sellerId)
            ->setParameter('status', 'active')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

