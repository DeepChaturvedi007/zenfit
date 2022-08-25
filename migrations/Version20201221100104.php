<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201221100104 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meal_products ADD gluten_free_alternative_id INT UNSIGNED DEFAULT NULL, ADD lactose_free_alternative_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE meal_products ADD CONSTRAINT FK_D7A84128B0D9D16E FOREIGN KEY (gluten_free_alternative_id) REFERENCES meal_products (id)');
        $this->addSql('ALTER TABLE meal_products ADD CONSTRAINT FK_D7A8412816A2CD15 FOREIGN KEY (lactose_free_alternative_id) REFERENCES meal_products (id)');
        $this->addSql('CREATE INDEX IDX_D7A84128B0D9D16E ON meal_products (gluten_free_alternative_id)');
        $this->addSql('CREATE INDEX IDX_D7A8412816A2CD15 ON meal_products (lactose_free_alternative_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meal_products DROP FOREIGN KEY FK_D7A84128B0D9D16E');
        $this->addSql('ALTER TABLE meal_products DROP FOREIGN KEY FK_D7A8412816A2CD15');
        $this->addSql('DROP INDEX IDX_D7A84128B0D9D16E ON meal_products');
        $this->addSql('DROP INDEX IDX_D7A8412816A2CD15 ON meal_products');
        $this->addSql('ALTER TABLE meal_products DROP gluten_free_alternative_id, DROP lactose_free_alternative_id');
    }
}
