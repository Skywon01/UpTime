<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224164756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention_consumed_part (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, intervention_id INT DEFAULT NULL, part_id INT DEFAULT NULL, INDEX IDX_8E6AE63C8EAE3863 (intervention_id), INDEX IDX_8E6AE63C4CE34BEC (part_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE intervention_consumed_part ADD CONSTRAINT FK_8E6AE63C8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervention_consumed_part ADD CONSTRAINT FK_8E6AE63C4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention_consumed_part DROP FOREIGN KEY FK_8E6AE63C8EAE3863');
        $this->addSql('ALTER TABLE intervention_consumed_part DROP FOREIGN KEY FK_8E6AE63C4CE34BEC');
        $this->addSql('DROP TABLE intervention_consumed_part');
    }
}
