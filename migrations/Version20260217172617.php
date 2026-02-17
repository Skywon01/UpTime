<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217172617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, designation VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, stock_quantity INT NOT NULL, supplier_id INT DEFAULT NULL, INDEX IDX_490F70C62ADD6D8C (supplier_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE part_machine (part_id INT NOT NULL, machine_id INT NOT NULL, INDEX IDX_9559288F4CE34BEC (part_id), INDEX IDX_9559288FF6B75B26 (machine_id), PRIMARY KEY (part_id, machine_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, contact_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(20) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C62ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE part_machine ADD CONSTRAINT FK_9559288F4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE part_machine ADD CONSTRAINT FK_9559288FF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C62ADD6D8C');
        $this->addSql('ALTER TABLE part_machine DROP FOREIGN KEY FK_9559288F4CE34BEC');
        $this->addSql('ALTER TABLE part_machine DROP FOREIGN KEY FK_9559288FF6B75B26');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE part_machine');
        $this->addSql('DROP TABLE supplier');
    }
}
