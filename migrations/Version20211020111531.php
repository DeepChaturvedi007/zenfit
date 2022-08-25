<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020111531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE user_subscriptions ADD vat VARCHAR(20) DEFAULT NULL');
        $this->addSql('UPDATE user_subscriptions, user_stripe SET user_subscriptions.vat = user_stripe.vat WHERE user_subscriptions.user_id = user_stripe.user_id');
        $this->addSql('ALTER TABLE user_stripe DROP vat');
        $this->addSql('UPDATE user_subscriptions SET attempt_count = 0 WHERE attempt_count IS NULL');
        $this->addSql('ALTER TABLE user_subscriptions CHANGE user_id user_id INT NOT NULL, CHANGE canceled_at canceled_at VARCHAR(15), CHANGE canceled canceled TINYINT(1) DEFAULT 0 NOT NULL, CHANGE attempt_count attempt_count INT NOT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
