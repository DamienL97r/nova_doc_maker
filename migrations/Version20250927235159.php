<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927235159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE owner (id SERIAL NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, sirene VARCHAR(255) NOT NULL, ape VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE quote_item DROP CONSTRAINT fk_8dfc7a94db805178');
        $this->addSql('DROP INDEX idx_8dfc7a94db805178');
        $this->addSql('ALTER TABLE quote_item DROP quote_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE owner');
        $this->addSql('ALTER TABLE quote_item ADD quote_id INT NOT NULL');
        $this->addSql('ALTER TABLE quote_item ADD CONSTRAINT fk_8dfc7a94db805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8dfc7a94db805178 ON quote_item (quote_id)');
    }
}
