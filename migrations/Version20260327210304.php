<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327210304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_message DROP CONSTRAINT fk_ba71692d7e3c61f9');
        $this->addSql('DROP INDEX idx_ba71692d7e3c61f9');
        $this->addSql('ALTER TABLE ticket_message ADD author_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket_message DROP owner_id');
        $this->addSql('ALTER TABLE ticket_message ADD CONSTRAINT FK_BA71692DF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_BA71692DF675F31B ON ticket_message (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_message DROP CONSTRAINT FK_BA71692DF675F31B');
        $this->addSql('DROP INDEX IDX_BA71692DF675F31B');
        $this->addSql('ALTER TABLE ticket_message ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_message DROP author_id');
        $this->addSql('ALTER TABLE ticket_message ADD CONSTRAINT fk_ba71692d7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ba71692d7e3c61f9 ON ticket_message (owner_id)');
    }
}
