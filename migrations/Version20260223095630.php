<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223095630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD requisites JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE game RENAME COLUMN photo TO photos');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD photo JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE game DROP photos');
        $this->addSql('ALTER TABLE game DROP requisites');
    }
}
