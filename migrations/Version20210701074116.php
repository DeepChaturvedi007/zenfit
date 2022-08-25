<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701074116 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients CHANGE demo_client demo_client TINYINT(1) DEFAULT NULL, CHANGE lasse_demo_client lasse_demo_client TINYINT(1) DEFAULT NULL');
        $this->addSql("UPDATE users set interactive_token = '' WHERE interactive_token IS NULL");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients CHANGE demo_client demo_client INT DEFAULT NULL, CHANGE lasse_demo_client lasse_demo_client INT DEFAULT NULL');
    }
}
