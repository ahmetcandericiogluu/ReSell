<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function save(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find existing conversation for listing between buyer and seller
     */
    public function findByListingAndParticipants(int $listingId, int $buyerId, int $sellerId): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.listingId = :listingId')
            ->andWhere('c.buyerId = :buyerId')
            ->andWhere('c.sellerId = :sellerId')
            ->setParameter('listingId', $listingId)
            ->setParameter('buyerId', $buyerId)
            ->setParameter('sellerId', $sellerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all conversations for a user (as buyer or seller)
     * Ordered by updated_at desc
     */
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.buyerId = :userId')
            ->orWhere('c.sellerId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find conversation by ID with messages eager loaded
     */
    public function findByIdWithMessages(string $id): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->addSelect('m')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

