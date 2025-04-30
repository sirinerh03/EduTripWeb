<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405145112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reservation_vol');
        $this->addSql('ALTER TABLE vol ADD aeropport_depart VARCHAR(255) NOT NULL, ADD aeropport_arrivee VARCHAR(255) NOT NULL, DROP aeroport_depart, DROP aeroport_arrivee, CHANGE id_vol id_vol INT AUTO_INCREMENT NOT NULL, CHANGE date_depart date_depart DATETIME NOT NULL, CHANGE date_arrivee date_arrivee DATETIME NOT NULL, ADD PRIMARY KEY (id_vol)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_vol (id INT AUTO_INCREMENT NOT NULL, date_reservation DATETIME NOT NULL, prix DOUBLE PRECISION NOT NULL, id_vol INT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, id_etudiant INT NOT NULL, nb_palce INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE vol MODIFY id_vol INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON vol');
        $this->addSql('ALTER TABLE vol ADD aeroport_depart VARCHAR(255) NOT NULL, ADD aeroport_arrivee VARCHAR(255) NOT NULL, DROP aeropport_depart, DROP aeropport_arrivee, CHANGE id_vol id_vol INT NOT NULL, CHANGE date_depart date_depart DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE date_arrivee date_arrivee DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }
}
