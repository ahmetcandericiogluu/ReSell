<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConversationParticipant>
 */
class ConversationParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationParticipant::class);
    }

    public function save(ConversationParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find participant record for user in conversation
     */
    public function findByConversationAndUser(Conversation|string $conversation, int $userId): ?ConversationParticipant
    {
        $qb = $this->createQueryBuilder('cp');

        if ($conversation instanceof Conversation) {
            $qb->where('cp.conversation = :conversation')
               ->andWhere('cp.userId = :userId')
               ->setParameter('conversation', $conversation)
               ->setParameter('userId', $userId);
        } else {
            // String UUID
            $qb->join('cp.conversation', 'c')
               ->where('c.id = :conversationId')
               ->andWhere('cp.userId = :userId')
               ->setParameter('conversationId', $conversation)
               ->setParameter('userId', $userId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get or create participant for user in conversation
     */
    public function getOrCreate(Conversation $conversation, int $userId): ConversationParticipant
    {
        $participant = $this->findByConversationAndUser($conversation, $userId);
        
        if ($participant === null) {
            $participant = new ConversationParticipant();
            $participant->setConversation($conversation);
            $participant->setUserId($userId);
            $this->save($participant, true);
        }
        
        return $participant;
    }
}

