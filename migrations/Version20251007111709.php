<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007111709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id UUID NOT NULL, company_name VARCHAR(255) NOT NULL, sirene VARCHAR(9) NOT NULL, ape VARCHAR(5) NOT NULL, vat_number VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(25) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_customer_sirene ON customer (sirene)');
        $this->addSql('CREATE UNIQUE INDEX uniq_customer_email ON customer (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_customer_vat ON customer (vat_number)');
        $this->addSql('COMMENT ON COLUMN customer.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE owner (id UUID NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, sirene VARCHAR(9) NOT NULL, ape VARCHAR(5) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(25) NOT NULL, company_name VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF60E67CE1DA79D2 ON owner (sirene)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF60E67CE7927C74 ON owner (email)');
        $this->addSql('COMMENT ON COLUMN owner.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE product (id UUID NOT NULL, quote_id UUID NOT NULL, description TEXT DEFAULT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04ADDB805178 ON product (quote_id)');
        $this->addSql('COMMENT ON COLUMN product.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN product.quote_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE quote (id UUID NOT NULL, customer_id UUID NOT NULL, owner_id UUID NOT NULL, number VARCHAR(40) NOT NULL, title VARCHAR(180) NOT NULL, issue_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, sub_total NUMERIC(10, 2) NOT NULL, tax_total NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6B71CBF496901F54 ON quote (number)');
        $this->addSql('CREATE INDEX IDX_6B71CBF49395C3F3 ON quote (customer_id)');
        $this->addSql('CREATE INDEX IDX_6B71CBF47E3C61F9 ON quote (owner_id)');
        $this->addSql('COMMENT ON COLUMN quote.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN quote.customer_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN quote.owner_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN quote.issue_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN quote.valid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADDB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF47E3C61F9 FOREIGN KEY (owner_id) REFERENCES owner (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADDB805178');
        $this->addSql('ALTER TABLE quote DROP CONSTRAINT FK_6B71CBF49395C3F3');
        $this->addSql('ALTER TABLE quote DROP CONSTRAINT FK_6B71CBF47E3C61F9');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE owner');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE quote');
    }
}
