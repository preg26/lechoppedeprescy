<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale pour PostgreSQL - Création des tables
 */
final class Version20260906000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables principales (Category, Creation, ContactRequest, PageContent, User) pour PostgreSQL';
    }

    public function up(Schema $schema): void
    {
        // Category table
        $this->addSql('CREATE TABLE category (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            CONSTRAINT UNIQ_64C19C1989D9B62 UNIQUE (slug)
        )');

        // Creation table
        $this->addSql('CREATE TABLE creation (
            id SERIAL PRIMARY KEY,
            category_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            images TEXT DEFAULT NULL,
            is_published BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT FK_57EE857412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_57EE857412469DE2 ON creation (category_id)');
        $this->addSql('COMMENT ON COLUMN creation.images IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN creation.created_at IS \'(DC2Type:datetime_immutable)\'');

        // Contact Request table
        $this->addSql('CREATE TABLE contact_request (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            message TEXT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            consulted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            CONSTRAINT UNIQ_6F7D8D9F5E237E06 UNIQUE (name)
        )');
        $this->addSql('COMMENT ON COLUMN contact_request.created_at IS \'(DC2Type:datetime_immutable)\'');

        // Page Content table
        $this->addSql('CREATE TABLE page_content (
            id SERIAL PRIMARY KEY,
            key VARCHAR(255) NOT NULL,
            value TEXT NOT NULL,
            section VARCHAR(100) NOT NULL,
            CONSTRAINT UNIQ_6BEB75A38A90ABA9 UNIQUE (key)
        )');

        // User table
        $this->addSql('CREATE TABLE "user" (
            id SERIAL PRIMARY KEY,
            email VARCHAR(180) NOT NULL,
            roles TEXT NOT NULL,
            password VARCHAR(255) NOT NULL,
            CONSTRAINT UNIQ_8D93D649E7927C74 UNIQUE (email)
        )');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE creation');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contact_request');
        $this->addSql('DROP TABLE page_content');
        $this->addSql('DROP TABLE "user"');
    }
}
