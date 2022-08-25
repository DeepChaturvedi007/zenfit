<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208095637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriptions ADD tax_rate_id VARCHAR(34) DEFAULT NULL, DROP clients, DROP price_year, DROP stripe_name_year, DROP workout_plans, DROP meal_plans, DROP `order`, DROP unlimited_clients, DROP trial, DROP popular, DROP send_push_notifications, DROP personalize_client, DROP save_plans_pdf, DROP inactive_clients, DROP description, DROP hidden, DROP contact_us');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE subscriptions ADD clients INT NOT NULL, ADD price_year INT NOT NULL, ADD stripe_name_year VARCHAR(30) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD workout_plans INT NOT NULL, ADD meal_plans INT NOT NULL, ADD `order` INT DEFAULT 0 NOT NULL, ADD unlimited_clients TINYINT(1) DEFAULT \'0\' NOT NULL, ADD trial TINYINT(1) DEFAULT \'0\' NOT NULL, ADD popular TINYINT(1) DEFAULT \'0\' NOT NULL, ADD send_push_notifications TINYINT(1) DEFAULT \'0\' NOT NULL, ADD personalize_client TINYINT(1) DEFAULT \'0\' NOT NULL, ADD save_plans_pdf TINYINT(1) DEFAULT \'0\' NOT NULL, ADD inactive_clients TINYINT(1) DEFAULT \'0\' NOT NULL, ADD description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD hidden TINYINT(1) DEFAULT \'0\' NOT NULL, ADD contact_us TINYINT(1) DEFAULT \'0\' NOT NULL, DROP tax_rate_id');
    }
}
