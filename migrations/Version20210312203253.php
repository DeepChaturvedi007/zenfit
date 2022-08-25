<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210312203253 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_stripe DROP INDEX IDX_C1A3F767A76ED395, ADD UNIQUE INDEX UNIQ_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_stripe ADD klarna_country VARCHAR(2) DEFAULT NULL, DROP fee, CHANGE only_percentage klarna_enabled TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_stripe DROP INDEX UNIQ_C1A3F767A76ED395, ADD INDEX IDX_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_stripe ADD fee INT DEFAULT NULL, DROP klarna_country, CHANGE klarna_enabled only_percentage TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
