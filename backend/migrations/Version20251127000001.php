<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create listings table
 */
final class Version20251127000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create listings table with seller relation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE listings (
            id BIGSERIAL PRIMARY KEY,
            seller_id BIGINT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(10) NOT NULL DEFAULT \'TRY\',
            status VARCHAR(20) NOT NULL DEFAULT \'draft\',
            category_id BIGINT NULL,
            location VARCHAR(255) NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT fk_listings_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
        )');

        $this->addSql('CREATE INDEX idx_listings_seller ON listings (seller_id)');
        $this->addSql('CREATE INDEX idx_listings_status ON listings (status)');
        $this->addSql('CREATE INDEX idx_listings_category ON listings (category_id)');
        
        $this->addSql('COMMENT ON COLUMN listings.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN listings.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE listings');
    }
}

