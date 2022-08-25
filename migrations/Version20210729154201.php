<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210729154201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE meal_product_language CHANGE meal_product_id meal_product_id INT UNSIGNED NOT NULL, CHANGE language_id language_id INT NOT NULL');
        $this->addSql('ALTER TABLE payments CHANGE recurring_fee recurring_fee VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE meal_product_language CHANGE meal_product_id meal_product_id INT UNSIGNED DEFAULT NULL, CHANGE language_id language_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payments CHANGE recurring_fee recurring_fee VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
