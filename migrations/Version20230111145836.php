<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111145836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_event ADD prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_event ADD CONSTRAINT FK_F59B7F9CD182060A FOREIGN KEY (prospect_id) REFERENCES "prospect" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F59B7F9CD182060A ON customer_event (prospect_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "customer_event" DROP CONSTRAINT FK_F59B7F9CD182060A');
        $this->addSql('DROP INDEX IDX_F59B7F9CD182060A');
        $this->addSql('ALTER TABLE "customer_event" DROP prospect_id');
    }
}
