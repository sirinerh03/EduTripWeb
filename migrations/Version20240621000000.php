<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240621000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le champ confirmation_token_created_at à la table user';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la colonne existe déjà
        $checkSql = "SHOW COLUMNS FROM `user` LIKE 'confirmation_token_created_at'";
        $result = $this->connection->executeQuery($checkSql)->fetchAllAssociative();
        
        if (empty($result)) {
            $this->addSql('ALTER TABLE user ADD confirmation_token_created_at DATETIME DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP confirmation_token_created_at');
    }
}
