<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250928003507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE owner ADD company_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE owner ADD logo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE owner DROP company_name');
        $this->addSql('ALTER TABLE owner DROP logo');
    }
}
