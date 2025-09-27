<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250927215429 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer (id SERIAL NOT NULL, company_name VARCHAR(255) NOT NULL, sirene VARCHAR(255) NOT NULL, ape VARCHAR(255) NOT NULL, vat_number VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quote (id SERIAL NOT NULL, customer_id INT NOT NULL, number VARCHAR(40) NOT NULL, title VARCHAR(180) NOT NULL, issue_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, sub_total NUMERIC(10, 2) DEFAULT NULL, tax_total NUMERIC(10, 2) DEFAULT NULL, total NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6B71CBF49395C3F3 ON quote (customer_id)');
        $this->addSql('COMMENT ON COLUMN quote.issue_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN quote.valid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE quote_item (id SERIAL NOT NULL, description TEXT DEFAULT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quote DROP CONSTRAINT FK_6B71CBF49395C3F3');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE quote_item');
    }
}
