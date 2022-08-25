<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209114046 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stripe_connect DROP INDEX UNIQ_5B08C8E5A76ED395, ADD INDEX IDX_5B08C8E5A76ED395 (user_id)');
        $this->addSql('ALTER TABLE stripe_connect DROP FOREIGN KEY FK_5B08C8E5A76ED395');
        $this->addSql('ALTER TABLE stripe_connect DROP updated_at, DROP count, CHANGE amount amount NUMERIC(8, 2) NOT NULL, CHANGE currency currency VARCHAR(10) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_stripe DROP fee');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stripe_connect DROP INDEX IDX_5B08C8E5A76ED395, ADD UNIQUE INDEX UNIQ_5B08C8E5A76ED395 (user_id)');
        $this->addSql('ALTER TABLE stripe_connect DROP FOREIGN KEY FK_5B08C8E5A76ED395');
        $this->addSql('ALTER TABLE stripe_connect ADD updated_at DATETIME DEFAULT NULL, ADD count INT DEFAULT 0 NOT NULL, CHANGE amount amount NUMERIC(8, 2) DEFAULT NULL, CHANGE currency currency VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_stripe ADD fee INT DEFAULT NULL');
    }
}
