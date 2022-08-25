<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716063229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_images CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE client_macros CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE user_id user_id INT NOT NULL, CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE gyms CHANGE admin_id admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE queue CHANGE survey survey TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE recipes CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE messages CHANGE sent_at sent_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE referrals CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_subscriptions CHANGE user_id user_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_images CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE client_macros CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE user_id user_id INT DEFAULT NULL, CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE gyms CHANGE admin_id admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE queue CHANGE survey survey INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipes CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messages CHANGE sent_at sent_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE referrals CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_subscriptions CHANGE user_id user_id INT DEFAULT NULL');
    }
}
