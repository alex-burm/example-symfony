<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724111431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user 
                ADD COLUMN confirmation_code VARCHAR(100) DEFAULT NULL AFTER password
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user 
                DROP COLUMN confirmation_code
        ');
    }
}
