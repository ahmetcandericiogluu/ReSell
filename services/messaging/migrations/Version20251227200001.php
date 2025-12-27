<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227200001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add buyer_name and seller_name columns to conversations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversations ADD COLUMN buyer_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conversations ADD COLUMN seller_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversations DROP COLUMN buyer_name');
        $this->addSql('ALTER TABLE conversations DROP COLUMN seller_name');
    }
}

