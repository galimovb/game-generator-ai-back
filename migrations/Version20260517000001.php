<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game_judge_log table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE game_judge_log (
            id SERIAL NOT NULL,
            game_id INT NOT NULL,
            score DOUBLE PRECISION NOT NULL,
            passed BOOLEAN NOT NULL,
            is_safe BOOLEAN NOT NULL,
            criteria JSON DEFAULT NULL,
            safety_issues JSON DEFAULT NULL,
            fail_reason TEXT DEFAULT NULL,
            attempt INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_game_judge_log_game ON game_judge_log (game_id)');
        $this->addSql('ALTER TABLE game_judge_log ADD CONSTRAINT FK_game_judge_log_game FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('COMMENT ON COLUMN game_judge_log.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_judge_log DROP CONSTRAINT FK_game_judge_log_game');
        $this->addSql('DROP TABLE game_judge_log');
    }
}
