<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200913134614 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD update_meal_schedule INT DEFAULT NULL, CHANGE update_plans_schedule update_workout_schedule INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
        $this->addSql('INSERT INTO events (name,title,notify_trainer,priority) VALUES ("client.update_workout_plan", "Update client workout plan", NULL, 9)');
        $this->addSql('INSERT INTO events (name,title,notify_trainer,priority) VALUES ("client.update_meal_plan", "Update client meal plan", NULL, 9)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE clients ADD update_plans_schedule INT DEFAULT NULL, DROP update_workout_schedule, DROP update_meal_schedule');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
