<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201209142054 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payments_log ADD arrival_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payments_log ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payments_log ADD CONSTRAINT FK_E7E6244EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payments_log DROP arrival_date');
        $this->addSql('ALTER TABLE payments_log DROP FOREIGN KEY FK_E7E6244EA76ED395');
        $this->addSql('ALTER TABLE payments_log DROP user_id');
    }
}
