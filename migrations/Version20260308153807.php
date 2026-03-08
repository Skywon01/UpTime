<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308153807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE part ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_490F70C6979B1AD6 ON part (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C6979B1AD6');
        $this->addSql('DROP INDEX IDX_490F70C6979B1AD6 ON part');
        $this->addSql('ALTER TABLE part DROP company_id');
    }
}
