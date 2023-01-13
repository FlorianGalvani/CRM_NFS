<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230113100414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a769b6b5fba');
        $this->addSql('DROP INDEX idx_d8698a769b6b5fba');
        $this->addSql('ALTER TABLE document ADD commercial_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE document RENAME COLUMN account_id TO customer_id');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A769395C3F3 FOREIGN KEY (customer_id) REFERENCES "account" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A767854071C FOREIGN KEY (commercial_id) REFERENCES "account" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D8698A769395C3F3 ON document (customer_id)');
        $this->addSql('CREATE INDEX IDX_D8698A767854071C ON document (commercial_id)');
        $this->addSql('ALTER TABLE "user" ADD email_verified BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD email_verification_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD email_verification_token_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP email_verified');
        $this->addSql('ALTER TABLE "user" DROP email_verification_token');
        $this->addSql('ALTER TABLE "user" DROP email_verification_token_at');
        $this->addSql('ALTER TABLE "document" DROP CONSTRAINT FK_D8698A769395C3F3');
        $this->addSql('ALTER TABLE "document" DROP CONSTRAINT FK_D8698A767854071C');
        $this->addSql('DROP INDEX IDX_D8698A769395C3F3');
        $this->addSql('DROP INDEX IDX_D8698A767854071C');
        $this->addSql('ALTER TABLE "document" ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "document" DROP customer_id');
        $this->addSql('ALTER TABLE "document" DROP commercial_id');
        $this->addSql('ALTER TABLE "document" DROP data');
        $this->addSql('ALTER TABLE "document" ADD CONSTRAINT fk_d8698a769b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d8698a769b6b5fba ON "document" (account_id)');
    }
}
