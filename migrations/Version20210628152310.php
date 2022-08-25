<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210628152310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payments_log ADD stripe_connect_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE payments_log ADD CONSTRAINT FK_E7E6244EC4900254 FOREIGN KEY (stripe_connect_id) REFERENCES stripe_connect (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payments_log DROP stripe_connect_id');
        $this->addSql('ALTER TABLE payments_log RENAME INDEX idx_e7e6244ea76ed395 TO FK_E7E6244EA76ED395');
    }
}
