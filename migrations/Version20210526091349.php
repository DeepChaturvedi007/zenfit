<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526091349 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE clients SET demo_client = 0 WHERE demo_client IS NULL');
        $this->addSql('UPDATE clients SET lasse_demo_client = 0 WHERE lasse_demo_client IS NULL');
        $this->addSql('UPDATE clients SET accept_terms = 0 WHERE accept_terms IS NULL');
        $this->addSql('UPDATE clients SET accept_email_notifications = 0 WHERE accept_email_notifications IS NULL');
        $this->addSql('UPDATE clients SET locale = "en" WHERE locale IS NULL');
        $this->addSql('ALTER TABLE clients DROP trainer_viewed');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD trainer_viewed TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
