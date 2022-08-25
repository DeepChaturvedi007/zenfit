<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210802201505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients CHANGE workout_location workout_location INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messages CHANGE conversation_id conversation_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_app CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_stripe CHANGE fee_percentage fee_percentage DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE user_terms CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_clients CHANGE video_id video_id INT UNSIGNED NOT NULL, CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE video_tags CHANGE video_id video_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE videos CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE workout_plan_meta CHANGE plan_id plan_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE workout_plans_settings CHANGE plan_id plan_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients CHANGE workout_location workout_location VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE messages CHANGE conversation_id conversation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_app CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_stripe CHANGE fee_percentage fee_percentage NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_terms CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_clients CHANGE video_id video_id INT UNSIGNED DEFAULT NULL, CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE video_tags CHANGE video_id video_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE videos CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workout_plan_meta CHANGE plan_id plan_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE workout_plans_settings CHANGE plan_id plan_id INT UNSIGNED DEFAULT NULL');
    }
}
