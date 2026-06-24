<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624093938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "company" (id UUID NOT NULL, name VARCHAR(255) NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4FBF094F7E3C61F9 ON "company" (owner_id)');
        $this->addSql('CREATE TABLE "order" (id UUID NOT NULL, amountRub NUMERIC(10, 2) NOT NULL, rateEur NUMERIC(10, 4) NOT NULL, amountEur NUMERIC(10, 2) NOT NULL, company_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F5299398979B1AD6 ON "order" (company_id)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE "company" ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398979B1AD6 FOREIGN KEY (company_id) REFERENCES "company" (id) ON DELETE RESTRICT NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "company" DROP CONSTRAINT FK_4FBF094F7E3C61F9');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398979B1AD6');
        $this->addSql('DROP TABLE "company"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE "user"');
    }
}
