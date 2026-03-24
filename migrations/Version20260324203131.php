<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260324203131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_message ADD text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_message ADD photos JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_message ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE ticket_message ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_message ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_message ADD CONSTRAINT FK_BA71692D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_BA71692D7E3C61F9 ON ticket_message (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_message DROP CONSTRAINT FK_BA71692D7E3C61F9');
        $this->addSql('DROP INDEX IDX_BA71692D7E3C61F9');
        $this->addSql('ALTER TABLE ticket_message DROP text');
        $this->addSql('ALTER TABLE ticket_message DROP photos');
        $this->addSql('ALTER TABLE ticket_message DROP created_at');
        $this->addSql('ALTER TABLE ticket_message DROP updated_at');
        $this->addSql('ALTER TABLE ticket_message DROP owner_id');
    }
}
