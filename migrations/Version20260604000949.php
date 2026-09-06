<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604000949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__creation AS SELECT id, title, description, created_at, is_published, category_id FROM creation');
        $this->addSql('DROP TABLE creation');
        $this->addSql('CREATE TABLE creation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, category_id INTEGER NOT NULL, images CLOB DEFAULT NULL, CONSTRAINT FK_57EE857412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO creation (id, title, description, created_at, is_published, category_id) SELECT id, title, description, created_at, is_published, category_id FROM __temp__creation');
        $this->addSql('DROP TABLE __temp__creation');
        $this->addSql('CREATE INDEX IDX_57EE857412469DE2 ON creation (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__creation AS SELECT id, title, description, created_at, is_published, category_id FROM creation');
        $this->addSql('DROP TABLE creation');
        $this->addSql('CREATE TABLE creation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, category_id INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_57EE857412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO creation (id, title, description, created_at, is_published, category_id) SELECT id, title, description, created_at, is_published, category_id FROM __temp__creation');
        $this->addSql('DROP TABLE __temp__creation');
        $this->addSql('CREATE INDEX IDX_57EE857412469DE2 ON creation (category_id)');
    }
}
