<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add OAuth fields to User entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD google_id VARCHAR(255) DEFAULT NULL, ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD github_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP google_id, DROP facebook_id, DROP github_id');
    }
}
