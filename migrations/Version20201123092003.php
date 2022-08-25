<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201123092003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients DROP update_plans_schedule');
        $this->addSql('ALTER TABLE meal_plans ADD CONSTRAINT FK_8FAD700759D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE queue DROP INDEX IDX_7FFD7F634C3A3BB, ADD UNIQUE INDEX UNIQ_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE recipes DROP portions');
        $this->addSql('ALTER TABLE user_settings DROP mail_chimp_api_key, DROP mail_chimp_list_id');
        $this->addSql('ALTER TABLE user_stripe DROP INDEX IDX_C1A3F767A76ED395, ADD UNIQUE INDEX UNIQ_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE users ADD version INT DEFAULT 1 NOT NULL, CHANGE hide_nutritional_facts_in_app hide_nutritional_facts_in_app TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD update_plans_schedule INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meal_plans DROP FOREIGN KEY FK_8FAD700759D8A214');
        $this->addSql('ALTER TABLE queue DROP INDEX UNIQ_7FFD7F634C3A3BB, ADD INDEX IDX_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE recipes ADD portions INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE user_settings ADD mail_chimp_api_key VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD mail_chimp_list_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_stripe DROP INDEX UNIQ_C1A3F767A76ED395, ADD INDEX IDX_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE users DROP version, CHANGE hide_nutritional_facts_in_app hide_nutritional_facts_in_app TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
