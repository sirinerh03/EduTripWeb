<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414225459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis CHANGE commentaire commentaire LONGTEXT NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE avis RENAME INDEX avis_ibfk_1 TO IDX_8F91ABF0A76ED395');
        $this->addSql('ALTER TABLE user CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE mail mail VARCHAR(255) NOT NULL, CHANGE tel tel VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE confirm_password confirm_password VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A76ED395');
        $this->addSql('ALTER TABLE avis CHANGE commentaire commentaire TEXT NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf0a76ed395 TO avis_ibfk_1');
        $this->addSql('ALTER TABLE user CHANGE prenom prenom VARCHAR(100) NOT NULL, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE mail mail VARCHAR(100) NOT NULL, CHANGE tel tel VARCHAR(100) NOT NULL, CHANGE status status VARCHAR(100) NOT NULL, CHANGE role role VARCHAR(100) NOT NULL, CHANGE password password VARCHAR(100) NOT NULL, CHANGE confirm_password confirm_password VARCHAR(100) NOT NULL');
    }
}
