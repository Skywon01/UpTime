<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210164050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, machine_id INT NOT NULL, technician_id INT DEFAULT NULL, INDEX IDX_D11814ABF6B75B26 (machine_id), INDEX IDX_D11814ABE6C5D496 (technician_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, brand VARCHAR(255) DEFAULT NULL, serial_number VARCHAR(100) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_1505DF84979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABE6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF84979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABF6B75B26');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABE6C5D496');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF84979B1AD6');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE machine');
    }
}
