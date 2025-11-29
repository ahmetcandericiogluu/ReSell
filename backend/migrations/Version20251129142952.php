<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129142952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_images (id BIGSERIAL NOT NULL, listing_id BIGINT NOT NULL, storage_driver VARCHAR(20) NOT NULL, path VARCHAR(500) NOT NULL, url VARCHAR(1000) NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4E79FB9D4619D1A ON listing_images (listing_id)');
        $this->addSql('COMMENT ON COLUMN listing_images.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE listing_images ADD CONSTRAINT FK_4E79FB9D4619D1A FOREIGN KEY (listing_id) REFERENCES listings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listings DROP CONSTRAINT fk_listings_seller');
        $this->addSql('DROP INDEX idx_listings_category');
        $this->addSql('DROP INDEX idx_listings_status');
        $this->addSql('ALTER TABLE listings ALTER currency DROP DEFAULT');
        $this->addSql('ALTER TABLE listings ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE listings ADD CONSTRAINT FK_9A7BD98E8DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_listings_seller RENAME TO IDX_9A7BD98E8DE820D9');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE listing_images DROP CONSTRAINT FK_4E79FB9D4619D1A');
        $this->addSql('DROP TABLE listing_images');
        $this->addSql('ALTER TABLE listings DROP CONSTRAINT FK_9A7BD98E8DE820D9');
        $this->addSql('ALTER TABLE listings ALTER currency SET DEFAULT \'TRY\'');
        $this->addSql('ALTER TABLE listings ALTER status SET DEFAULT \'draft\'');
        $this->addSql('ALTER TABLE listings ADD CONSTRAINT fk_listings_seller FOREIGN KEY (seller_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_listings_category ON listings (category_id)');
        $this->addSql('CREATE INDEX idx_listings_status ON listings (status)');
        $this->addSql('ALTER INDEX idx_9a7bd98e8de820d9 RENAME TO idx_listings_seller');
    }
}
