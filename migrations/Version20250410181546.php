<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410181546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_vol ADD CONSTRAINT FK_5C5EBA3497F87FB1 FOREIGN KEY (id_vol) REFERENCES vol (id_vol)');
        $this->addSql('CREATE INDEX IDX_5C5EBA3497F87FB1 ON reservation_vol (id_vol)');
        $this->addSql('ALTER TABLE vol ADD aeroport_depart VARCHAR(255) NOT NULL, ADD aeroport_arrivee VARCHAR(255) NOT NULL, DROP aeropport_depart, DROP aeropport_arrivee');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_vol DROP FOREIGN KEY FK_5C5EBA3497F87FB1');
        $this->addSql('DROP INDEX IDX_5C5EBA3497F87FB1 ON reservation_vol');
        $this->addSql('ALTER TABLE vol ADD aeropport_depart VARCHAR(255) NOT NULL, ADD aeropport_arrivee VARCHAR(255) NOT NULL, DROP aeroport_depart, DROP aeroport_arrivee');
    }
}
