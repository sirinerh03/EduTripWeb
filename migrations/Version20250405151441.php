<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405151441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE hebergement (id_hebergement INT NOT NULL, nomh VARCHAR(255) NOT NULL, typeh VARCHAR(255) NOT NULL, adressh VARCHAR(255) NOT NULL, capaciteh INT NOT NULL, prixh DOUBLE PRECISION NOT NULL, disponibleh VARCHAR(255) NOT NULL, descriptionh VARCHAR(255) NOT NULL, imageh VARCHAR(255) NOT NULL, PRIMARY KEY(id_hebergement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation_hebergement (id_reservationh INT NOT NULL, id_hebergement INT DEFAULT NULL, date_d DATE NOT NULL, date_f DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_843E00C05040106B (id_hebergement), PRIMARY KEY(id_reservationh)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hebergement ADD CONSTRAINT FK_843E00C05040106B FOREIGN KEY (id_hebergement) REFERENCES hebergement (id_hebergement) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hebergement DROP FOREIGN KEY FK_843E00C05040106B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hebergement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation_hebergement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
