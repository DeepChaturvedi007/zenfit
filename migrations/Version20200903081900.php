<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200903081900 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD type INT DEFAULT 0 NOT NULL, DROP meal_locale');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
        $this->addSql('INSERT INTO events (name,title,notify_trainer,priority) VALUES ("client.create_workout_plan", "Create client workout plan", NULL, 9)');
        $this->addSql('INSERT INTO events (name,title,notify_trainer,priority) VALUES ("client.create_meal_plan", "Create client meal plan", NULL, 9)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD meal_locale VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, DROP type');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
