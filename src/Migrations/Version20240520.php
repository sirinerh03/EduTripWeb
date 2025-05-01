<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes is_verified et confirmation_token à la table user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE user ADD confirmation_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP is_verified');
        $this->addSql('ALTER TABLE user DROP confirmation_token');
    }
}
