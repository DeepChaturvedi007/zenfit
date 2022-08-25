<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622082558 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE clients set email = '' where email is null");
        $this->addSql("UPDATE payments SET months = 0 WHERE months IS NULL");
        $this->addSql("UPDATE payments SET recurring_fee = 0 WHERE recurring_fee IS NULL");
        $this->addSql("UPDATE payments SET datakey = '' WHERE datakey IS NULL");
        $this->addSql("UPDATE payments SET currency = '' WHERE currency IS NULL");
    }

    public function down(Schema $schema) : void
    {

    }
}
