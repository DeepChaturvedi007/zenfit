<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210530093553 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE workout_plans set deleted = 0 WHERE deleted IS NULL');
        $this->addSql('CREATE TABLE workout_plan_tags (id INT AUTO_INCREMENT NOT NULL, workout_plan_id INT UNSIGNED DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_58B03F80945F6E33 (workout_plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE workout_plan_tags ADD CONSTRAINT FK_58B03F80945F6E33 FOREIGN KEY (workout_plan_id) REFERENCES workout_plans (id)');
        $this->addSql('ALTER TABLE workout_plans DROP active, CHANGE deleted deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE workout_plan_tags');
        $this->addSql('ALTER TABLE workout_plans ADD active TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
