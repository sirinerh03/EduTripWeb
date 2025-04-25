<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414235631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE university (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8228E9B06 FOREIGN KEY (idUniversity) REFERENCES university (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8FE6E88D7 FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8228E9B06
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE university
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8FE6E88D7
        SQL);
    }
}
