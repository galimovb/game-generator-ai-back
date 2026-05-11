<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505185314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location_description column to game table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game ADD location_description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game DROP location_description');
    }
}
