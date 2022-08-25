<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210606115510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE clients SET workout_location = 1 WHERE workout_location LIKE "%treningsstudio%"');
        $this->addSql('UPDATE clients SET workout_location = 1 WHERE workout_location LIKE "%treningssenter%"');
        $this->addSql('UPDATE clients SET workout_location = 1 WHERE workout_location LIKE "%gym%"');
        $this->addSql('UPDATE clients SET workout_location = 1 WHERE workout_location LIKE "%træningscenter%"');
        $this->addSql('UPDATE clients SET workout_location = 2 WHERE workout_location LIKE "%hemma%"');
        $this->addSql('UPDATE clients SET workout_location = 2 WHERE workout_location LIKE "%hjemme%"');
        $this->addSql('UPDATE clients SET workout_location = 2 WHERE workout_location LIKE "%home%"');
        $this->addSql('UPDATE clients SET workout_location = 3 WHERE workout_location LIKE "%udenfor%"');
        $this->addSql('UPDATE clients SET workout_location = 3 WHERE workout_location LIKE "%outdoor%"');

        $this->addSql('ALTER TABLE clients CHANGE workout_location workout_location INT DEFAULT NULL');
        $this->addSql('ALTER TABLE clients ADD experience_level INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
