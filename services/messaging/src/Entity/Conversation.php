<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversations')]
#[ORM\UniqueConstraint(name: 'unique_conversation', columns: ['listing_id', 'buyer_id', 'seller_id'])]
#[ORM\Index(columns: ['buyer_id'], name: 'idx_conversation_buyer')]
#[ORM\Index(columns: ['seller_id'], name: 'idx_conversation_seller')]
#[ORM\Index(columns: ['listing_id'], name: 'idx_conversation_listing')]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'integer')]
    private int $listingId;

    #[ORM\Column(type: 'integer')]
    private int $buyerId;

    #[ORM\Column(type: 'integer')]
    private int $sellerId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $listingTitle = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $participants;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getListingId(): int
    {
        return $this->listingId;
    }

    public function setListingId(int $listingId): self
    {
        $this->listingId = $listingId;
        return $this;
    }

    public function getBuyerId(): int
    {
        return $this->buyerId;
    }

    public function setBuyerId(int $buyerId): self
    {
        $this->buyerId = $buyerId;
        return $this;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function setSellerId(int $sellerId): self
    {
        $this->sellerId = $sellerId;
        return $this;
    }

    public function getListingTitle(): ?string
    {
        return $this->listingTitle;
    }

    public function setListingTitle(?string $listingTitle): self
    {
        $this->listingTitle = $listingTitle;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }
        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setConversation($this);
        }
        return $this;
    }

    public function isParticipant(int $userId): bool
    {
        return $this->buyerId === $userId || $this->sellerId === $userId;
    }

    public function getLastMessage(): ?Message
    {
        if ($this->messages->isEmpty()) {
            return null;
        }
        return $this->messages->last() ?: null;
    }
}

