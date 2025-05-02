<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240517000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table spin_reward et les relations avec avis';
    }

    public function up(Schema $schema): void
    {
        // Créer la table spin_reward
        $this->addSql('CREATE TABLE spin_reward (
            id INT AUTO_INCREMENT NOT NULL,
            percentage INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajouter les colonnes à la table avis
        $this->addSql('ALTER TABLE avis ADD spin_reward_id INT DEFAULT NULL, ADD reward_claimed TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0B9CB2BE1 FOREIGN KEY (spin_reward_id) REFERENCES spin_reward (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0B9CB2BE1 ON avis (spin_reward_id)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les contraintes et colonnes de la table avis
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0B9CB2BE1');
        $this->addSql('DROP INDEX IDX_8F91ABF0B9CB2BE1 ON avis');
        $this->addSql('ALTER TABLE avis DROP spin_reward_id, DROP reward_claimed');

        // Supprimer la table spin_reward
        $this->addSql('DROP TABLE spin_reward');
    }
}
