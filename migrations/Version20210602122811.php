<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210602122811 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gyms ADD assign_data_from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gyms ADD CONSTRAINT FK_C5CA7615410FB70E FOREIGN KEY (assign_data_from_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gyms DROP FOREIGN KEY FK_C5CA7615410FB70E');
        $this->addSql('ALTER TABLE gyms DROP assign_data_from_id');
    }
}
