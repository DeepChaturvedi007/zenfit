<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210303140323 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE meal_product_meta (id INT UNSIGNED AUTO_INCREMENT NOT NULL, meal_product_id INT UNSIGNED DEFAULT NULL, lactose TINYINT(1) DEFAULT \'0\' NOT NULL, gluten TINYINT(1) DEFAULT \'0\' NOT NULL, nuts TINYINT(1) DEFAULT \'0\' NOT NULL, eggs TINYINT(1) DEFAULT \'0\' NOT NULL, pig TINYINT(1) DEFAULT \'0\' NOT NULL, shellfish TINYINT(1) DEFAULT \'0\' NOT NULL, fish TINYINT(1) DEFAULT \'0\' NOT NULL, is_vegetarian TINYINT(1) DEFAULT \'0\' NOT NULL, is_vegan TINYINT(1) DEFAULT \'0\' NOT NULL, is_pescetarian TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_B974C780D2E8D0D0 (meal_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meal_product_meta ADD CONSTRAINT FK_B974C780D2E8D0D0 FOREIGN KEY (meal_product_id) REFERENCES meal_products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE meal_product_meta');
    }
}
