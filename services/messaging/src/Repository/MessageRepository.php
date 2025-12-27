<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get paginated messages for a conversation
     */
    public function findByConversationPaginated(Conversation $conversation, int $page = 1, int $limit = 30): array
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total messages in a conversation
     */
    public function countByConversation(Conversation $conversation): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get unread count for a user in a conversation
     * Unread = messages after lastReadMessage AND not sent by this user
     */
    public function countUnreadForUser(Conversation $conversation, int $userId, ?Uuid $lastReadMessageId): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->andWhere('m.senderId != :userId')
            ->setParameter('conversation', $conversation)
            ->setParameter('userId', $userId);

        if ($lastReadMessageId !== null) {
            // Get the createdAt of last read message to compare
            $lastReadMessage = $this->find($lastReadMessageId);
            if ($lastReadMessage) {
                $qb->andWhere('m.createdAt > :lastReadAt')
                   ->setParameter('lastReadAt', $lastReadMessage->getCreatedAt());
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get the latest message in a conversation
     */
    public function findLatestByConversation(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

