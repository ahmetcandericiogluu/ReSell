<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251207100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial tables for listing service: categories, listings, listing_images';
    }

    public function up(Schema $schema): void
    {
        // Create categories table
        $this->addSql('CREATE TABLE categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            parent_id INTEGER DEFAULT NULL,
            CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) 
                REFERENCES categories (id) ON DELETE SET NULL
        )');

        // Create listings table
        $this->addSql('CREATE TABLE listings (
            id SERIAL PRIMARY KEY,
            seller_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT \'TRY\',
            status VARCHAR(50) NOT NULL DEFAULT \'active\',
            location VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            CONSTRAINT fk_listings_category FOREIGN KEY (category_id) 
                REFERENCES categories (id)
        )');

        // Add immutable marker comments for timestamp columns
        $this->addSql('COMMENT ON COLUMN listings.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN listings.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN listings.deleted_at IS \'(DC2Type:datetime_immutable)\'');

        // Create listing_images table
        $this->addSql('CREATE TABLE listing_images (
            id SERIAL PRIMARY KEY,
            listing_id INTEGER NOT NULL,
            url VARCHAR(500) NOT NULL,
            position INTEGER NOT NULL DEFAULT 0,
            CONSTRAINT fk_listing_images_listing FOREIGN KEY (listing_id) 
                REFERENCES listings (id) ON DELETE CASCADE
        )');

        // Create indexes
        $this->addSql('CREATE INDEX idx_listings_seller ON listings (seller_id)');
        $this->addSql('CREATE INDEX idx_listings_category ON listings (category_id)');
        $this->addSql('CREATE INDEX idx_listings_status ON listings (status)');
        $this->addSql('CREATE INDEX idx_listings_deleted ON listings (deleted_at)');
        $this->addSql('CREATE INDEX idx_listing_images_listing ON listing_images (listing_id)');
        $this->addSql('CREATE INDEX idx_categories_parent ON categories (parent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS listing_images CASCADE');
        $this->addSql('DROP TABLE IF EXISTS listings CASCADE');
        $this->addSql('DROP TABLE IF EXISTS categories CASCADE');
    }
}

