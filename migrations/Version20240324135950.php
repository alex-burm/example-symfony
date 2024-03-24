<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324135950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE IF NOT EXISTS activity (
                id         int unsigned auto_increment,
                url        varchar(255) not null,
                user_id    int(11) unsigned null,
                agent      varchar(255) null,
                query      text,
                ip_addr    varchar(100) not null,
                created_at datetime     not null,
                constraint id
                    primary key (id)
            );
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS activity');
    }
}
