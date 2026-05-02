<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502093832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD age INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD players INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD field_width INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD field_length INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD activity_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE game DROP min_age');
        $this->addSql('ALTER TABLE game DROP max_age');
        $this->addSql('ALTER TABLE game DROP min_players');
        $this->addSql('ALTER TABLE game DROP max_players');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD min_age INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD max_age INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD min_players INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD max_players INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game DROP age');
        $this->addSql('ALTER TABLE game DROP players');
        $this->addSql('ALTER TABLE game DROP field_width');
        $this->addSql('ALTER TABLE game DROP field_length');
        $this->addSql('ALTER TABLE game DROP activity_level');
    }
}
