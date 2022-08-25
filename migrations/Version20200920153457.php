<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200920153457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_reminders (id INT AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, title VARCHAR(32) NOT NULL, due_date DATE NOT NULL, resolved TINYINT(1) DEFAULT \'0\' NOT NULL, deleted TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_86B269DB19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_reminders ADD CONSTRAINT FK_86B269DB19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('UPDATE events SET name = "trainer.update_meal_plan" WHERE name = "client.update_meal_plan"');
        $this->addSql('UPDATE events SET name = "trainer.update_workout_plan" WHERE name = "client.update_workout_plan"');
        $this->addSql('UPDATE events SET name = "trainer.create_meal_plan" WHERE name = "client.create_meal_plan"');
        $this->addSql('UPDATE events SET name = "trainer.create_workout_plan" WHERE name = "client.create_workout_plan"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE client_reminders');
    }
}
