<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202151447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reviews (id BIGSERIAL NOT NULL, listing_id BIGINT NOT NULL, seller_id BIGINT NOT NULL, buyer_id BIGINT NOT NULL, rating SMALLINT NOT NULL, comment TEXT DEFAULT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6970EB0FD4619D1A ON reviews (listing_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F8DE820D9 ON reviews (seller_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F6C755722 ON reviews (buyer_id)');
        $this->addSql('COMMENT ON COLUMN reviews.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FD4619D1A FOREIGN KEY (listing_id) REFERENCES listings (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F8DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F6C755722 FOREIGN KEY (buyer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD rating_average NUMERIC(3, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE reviews DROP CONSTRAINT FK_6970EB0FD4619D1A');
        $this->addSql('ALTER TABLE reviews DROP CONSTRAINT FK_6970EB0F8DE820D9');
        $this->addSql('ALTER TABLE reviews DROP CONSTRAINT FK_6970EB0F6C755722');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('ALTER TABLE users DROP rating_average');
    }
}
