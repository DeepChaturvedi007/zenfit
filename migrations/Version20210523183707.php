<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210523183707 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE users set deleted = 0 WHERE deleted IS NULL');
        $this->addSql('ALTER TABLE users ADD leads_visible TINYINT(1) DEFAULT \'1\' NOT NULL, DROP facebook_id, DROP facebook_access_token, DROP pass, DROP visited_demo_client, DROP accept_terms, DROP version, CHANGE deleted deleted TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE activated activated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE hide_nutritional_facts_in_app hide_nutritional_facts_in_app TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('DROP TABLE user_referral');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD facebook_id BIGINT UNSIGNED DEFAULT NULL, ADD facebook_access_token VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD pass VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD visited_demo_client TINYINT(1) DEFAULT \'0\' NOT NULL, ADD accept_terms TINYINT(1) DEFAULT NULL, ADD version INT DEFAULT 1 NOT NULL, DROP leads_visible, CHANGE deleted deleted TINYINT(1) DEFAULT \'0\', CHANGE activated activated TINYINT(1) DEFAULT \'0\', CHANGE hide_nutritional_facts_in_app hide_nutritional_facts_in_app TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
