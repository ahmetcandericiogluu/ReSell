<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $review, bool $flush = false): void
    {
        $this->getEntityManager()->persist($review);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user, int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.seller = :user')
            ->andWhere('r.isPublic = :isPublic')
            ->setParameter('user', $user)
            ->setParameter('isPublic', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.seller = :user')
            ->andWhere('r.isPublic = :isPublic')
            ->setParameter('user', $user)
            ->setParameter('isPublic', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

