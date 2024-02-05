<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240122163605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE IF NOT EXISTS feedback (
                id         int unsigned auto_increment,
                name       varchar(255) not null,
                email      varchar(255) not null,
                subject    varchar(255) null,
                message    text         not null,
                created_at datetime     not null,
                constraint id
                    primary key (id)
            );
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS feedback');
    }
}
