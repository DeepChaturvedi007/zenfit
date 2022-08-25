<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211206095816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_clients CHANGE document_id document_id INT UNSIGNED NOT NULL, CHANGE client_id client_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_clients CHANGE document_id document_id INT UNSIGNED DEFAULT NULL, CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
    }
}
