<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230113164144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "account_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "customer_event_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "document_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "prospect_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "transaction_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "account" (id INT NOT NULL, commercial_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, account_status VARCHAR(255) NOT NULL, about TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D3656A47854071C ON "account" (commercial_id)');
        $this->addSql('CREATE TABLE "customer_event" (id INT NOT NULL, customer_id INT DEFAULT NULL, prospect_id INT DEFAULT NULL, events JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F59B7F9C9395C3F3 ON "customer_event" (customer_id)');
        $this->addSql('CREATE INDEX IDX_F59B7F9CD182060A ON "customer_event" (prospect_id)');
        $this->addSql('CREATE TABLE "document" (id INT NOT NULL, customer_id INT DEFAULT NULL, commercial_id INT DEFAULT NULL, transaction_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, file_extension VARCHAR(255) NOT NULL, data JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A769395C3F3 ON "document" (customer_id)');
        $this->addSql('CREATE INDEX IDX_D8698A767854071C ON "document" (commercial_id)');
        $this->addSql('CREATE INDEX IDX_D8698A762FC0CB0F ON "document" (transaction_id)');
        $this->addSql('CREATE TABLE "prospect" (id INT NOT NULL, commercial_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C9CE8C7D7854071C ON "prospect" (commercial_id)');
        $this->addSql('CREATE TABLE "transaction" (id INT NOT NULL, customer_id INT NOT NULL, transaction_quotation_id INT DEFAULT NULL, transaction_invoice_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, amount VARCHAR(255) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, payment_status VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_723705D19395C3F3 ON "transaction" (customer_id)');
        $this->addSql('CREATE INDEX IDX_723705D1F1899B00 ON "transaction" (transaction_quotation_id)');
        $this->addSql('CREATE INDEX IDX_723705D1C61FC64C ON "transaction" (transaction_invoice_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, account_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email_verified BOOLEAN NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, email_verification_token_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6499B6B5FBA ON "user" (account_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE "account" ADD CONSTRAINT FK_7D3656A47854071C FOREIGN KEY (commercial_id) REFERENCES "account" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "customer_event" ADD CONSTRAINT FK_F59B7F9C9395C3F3 FOREIGN KEY (customer_id) REFERENCES "account" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "customer_event" ADD CONSTRAINT FK_F59B7F9CD182060A FOREIGN KEY (prospect_id) REFERENCES "prospect" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "document" ADD CONSTRAINT FK_D8698A769395C3F3 FOREIGN KEY (customer_id) REFERENCES "account" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "document" ADD CONSTRAINT FK_D8698A767854071C FOREIGN KEY (commercial_id) REFERENCES "account" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "document" ADD CONSTRAINT FK_D8698A762FC0CB0F FOREIGN KEY (transaction_id) REFERENCES "transaction" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "prospect" ADD CONSTRAINT FK_C9CE8C7D7854071C FOREIGN KEY (commercial_id) REFERENCES "account" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "transaction" ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES "account" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "transaction" ADD CONSTRAINT FK_723705D1F1899B00 FOREIGN KEY (transaction_quotation_id) REFERENCES "document" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "transaction" ADD CONSTRAINT FK_723705D1C61FC64C FOREIGN KEY (transaction_invoice_id) REFERENCES "document" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6499B6B5FBA FOREIGN KEY (account_id) REFERENCES "account" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "account_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "customer_event_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "document_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "prospect_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "transaction_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE "account" DROP CONSTRAINT FK_7D3656A47854071C');
        $this->addSql('ALTER TABLE "customer_event" DROP CONSTRAINT FK_F59B7F9C9395C3F3');
        $this->addSql('ALTER TABLE "customer_event" DROP CONSTRAINT FK_F59B7F9CD182060A');
        $this->addSql('ALTER TABLE "document" DROP CONSTRAINT FK_D8698A769395C3F3');
        $this->addSql('ALTER TABLE "document" DROP CONSTRAINT FK_D8698A767854071C');
        $this->addSql('ALTER TABLE "document" DROP CONSTRAINT FK_D8698A762FC0CB0F');
        $this->addSql('ALTER TABLE "prospect" DROP CONSTRAINT FK_C9CE8C7D7854071C');
        $this->addSql('ALTER TABLE "transaction" DROP CONSTRAINT FK_723705D19395C3F3');
        $this->addSql('ALTER TABLE "transaction" DROP CONSTRAINT FK_723705D1F1899B00');
        $this->addSql('ALTER TABLE "transaction" DROP CONSTRAINT FK_723705D1C61FC64C');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6499B6B5FBA');
        $this->addSql('DROP TABLE "account"');
        $this->addSql('DROP TABLE "customer_event"');
        $this->addSql('DROP TABLE "document"');
        $this->addSql('DROP TABLE "prospect"');
        $this->addSql('DROP TABLE "transaction"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
