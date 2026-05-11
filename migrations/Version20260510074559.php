<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510074559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inn_checks (inn VARCHAR(12) NOT NULL, checked_at DATETIME NOT NULL, PRIMARY KEY (inn)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organizations (id INT AUTO_INCREMENT NOT NULL, ogrn VARCHAR(15) NOT NULL, name VARCHAR(255) NOT NULL, okved VARCHAR(15) NOT NULL, okved_type VARCHAR(15) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, inn VARCHAR(12) NOT NULL, UNIQUE INDEX UNIQ_427C1C7FB89AB2C7 (ogrn), INDEX IDX_427C1C7FE93323CB (inn), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE organizations ADD CONSTRAINT FK_427C1C7FE93323CB FOREIGN KEY (inn) REFERENCES inn_checks (inn)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organizations DROP FOREIGN KEY FK_427C1C7FE93323CB');
        $this->addSql('DROP TABLE inn_checks');
        $this->addSql('DROP TABLE organizations');
    }
}
