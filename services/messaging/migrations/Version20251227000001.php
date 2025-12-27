<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create messaging tables: conversations, conversation_participants, messages';
    }

    public function up(Schema $schema): void
    {
        // Conversations table
        $this->addSql('CREATE TABLE conversations (
            id UUID NOT NULL,
            listing_id INT NOT NULL,
            buyer_id INT NOT NULL,
            seller_id INT NOT NULL,
            listing_title VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_conversation_buyer ON conversations (buyer_id)');
        $this->addSql('CREATE INDEX idx_conversation_seller ON conversations (seller_id)');
        $this->addSql('CREATE INDEX idx_conversation_listing ON conversations (listing_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_conversation ON conversations (listing_id, buyer_id, seller_id)');
        $this->addSql('COMMENT ON COLUMN conversations.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN conversations.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversations.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Messages table
        $this->addSql('CREATE TABLE messages (
            id UUID NOT NULL,
            conversation_id UUID NOT NULL,
            sender_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_message_conversation_date ON messages (conversation_id, created_at)');
        $this->addSql('COMMENT ON COLUMN messages.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN messages.conversation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_MESSAGES_CONVERSATION FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Conversation participants table
        $this->addSql('CREATE TABLE conversation_participants (
            id SERIAL NOT NULL,
            conversation_id UUID NOT NULL,
            user_id INT NOT NULL,
            last_read_message_id UUID DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX unique_participant ON conversation_participants (conversation_id, user_id)');
        $this->addSql('COMMENT ON COLUMN conversation_participants.conversation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participants.last_read_message_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participants.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participants.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE conversation_participants ADD CONSTRAINT FK_CP_CONVERSATION FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participants ADD CONSTRAINT FK_CP_LAST_MESSAGE FOREIGN KEY (last_read_message_id) REFERENCES messages (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation_participants DROP CONSTRAINT FK_CP_LAST_MESSAGE');
        $this->addSql('ALTER TABLE conversation_participants DROP CONSTRAINT FK_CP_CONVERSATION');
        $this->addSql('ALTER TABLE messages DROP CONSTRAINT FK_MESSAGES_CONVERSATION');
        $this->addSql('DROP TABLE conversation_participants');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE conversations');
    }
}

