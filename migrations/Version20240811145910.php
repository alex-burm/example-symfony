<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811145910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE post 
                ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER content
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE post 
                DROP COLUMN image
        ');
    }
}
