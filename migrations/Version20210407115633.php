<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407115633 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX IDX_17904552F1FAD9D3 ON leads');
        $this->addSql('ALTER TABLE leads ADD updated_at DATETIME DEFAULT NULL, DROP type, DROP confirmed, DROP contacted, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE viewed viewed TINYINT(1) NOT NULL, CHANGE deleted deleted TINYINT(1) NOT NULL');
        $this->addSql('UPDATE leads set follow_up = 0 WHERE follow_up IS NULL');
        $this->addSql('UPDATE leads set status = 0 WHERE status IS NULL');
        $this->addSql('UPDATE leads set in_dialog = 0 WHERE in_dialog IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE leads ADD bundle_id INT UNSIGNED DEFAULT NULL, ADD type INT DEFAULT NULL, ADD confirmed TINYINT(1) DEFAULT \'0\' NOT NULL, ADD contacted TINYINT(1) DEFAULT \'0\' NOT NULL, DROP updated_at, CHANGE created_at created_at DATETIME NOT NULL, CHANGE viewed viewed INT DEFAULT 0 NOT NULL, CHANGE follow_up follow_up INT DEFAULT NULL, CHANGE in_dialog in_dialog INT DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE leads ADD CONSTRAINT FK_17904552F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES bundles (id) ON DELETE CASCADE');
    }
}
