<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604003255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page_content AS SELECT id, "key", value, section FROM page_content');
        $this->addSql('DROP TABLE page_content');
        $this->addSql('CREATE TABLE page_content (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "key" VARCHAR(255) NOT NULL, value CLOB NOT NULL, section VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO page_content (id, "key", value, section) SELECT id, "key", value, section FROM __temp__page_content');
        $this->addSql('DROP TABLE __temp__page_content');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page_content AS SELECT id, "key", value, section FROM page_content');
        $this->addSql('DROP TABLE page_content');
        $this->addSql('CREATE TABLE page_content (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "key" VARCHAR(255) NOT NULL, value CLOB NOT NULL, section VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO page_content (id, "key", value, section) SELECT id, "key", value, section FROM __temp__page_content');
        $this->addSql('DROP TABLE __temp__page_content');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A5DB3C8A90ABA9 ON page_content ("key")');
    }
}
