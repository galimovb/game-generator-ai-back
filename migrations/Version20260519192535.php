<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519192535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" RENAME TO "users"');
        $this->addSql('ALTER TABLE game RENAME TO games');
        $this->addSql('ALTER TABLE game_comment RENAME TO game_comments');
        $this->addSql('ALTER TABLE game_like RENAME TO game_likes');
        $this->addSql('ALTER TABLE game_stage RENAME TO game_stages');
        $this->addSql('ALTER TABLE ticket RENAME TO tickets');
        $this->addSql('ALTER TABLE ticket_message RENAME TO ticket_messages');
        $this->addSql('ALTER TABLE refresh_tokens RENAME TO user_refresh_tokens');
        $this->addSql('ALTER TABLE game_judge_log RENAME TO game_judge_logs');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "users" RENAME TO "user"');
        $this->addSql('ALTER TABLE games RENAME TO game');
        $this->addSql('ALTER TABLE game_comments RENAME TO game_comment');
        $this->addSql('ALTER TABLE game_likes RENAME TO game_like');
        $this->addSql('ALTER TABLE game_stages RENAME TO game_stage');
        $this->addSql('ALTER TABLE tickets RENAME TO ticket');
        $this->addSql('ALTER TABLE ticket_messages RENAME TO ticket_message');
        $this->addSql('ALTER TABLE user_refresh_tokens RENAME TO refresh_tokens');
        $this->addSql('ALTER TABLE game_judge_logs RENAME TO game_judge_log');
    }
}
