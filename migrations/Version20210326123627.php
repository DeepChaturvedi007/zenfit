<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210326123627 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE meal_product_meta ADD not_vegetarian TINYINT(1) DEFAULT \'0\' NOT NULL, ADD not_vegan TINYINT(1) DEFAULT \'0\' NOT NULL, ADD not_pescetarian TINYINT(1) DEFAULT \'0\' NOT NULL, DROP is_vegetarian, DROP is_vegan, DROP is_pescetarian');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE meal_product_meta ADD is_vegetarian TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_vegan TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_pescetarian TINYINT(1) DEFAULT \'0\' NOT NULL, DROP not_vegetarian, DROP not_vegan, DROP not_pescetarian');
    }
}
