<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240611000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les champs is_verified et confirmation_token à la table user';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si les colonnes existent déjà
        $table = $schema->getTable('user');
        
        if (!$table->hasColumn('is_verified')) {
            $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL DEFAULT 0');
        }
        
        if (!$table->hasColumn('confirmation_token')) {
            $this->addSql('ALTER TABLE user ADD confirmation_token VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP is_verified, DROP confirmation_token');
    }
}
