<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211021070518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('update client_stripe set paused = 0 where paused is null');
        $this->addSql('ALTER TABLE client_stripe CHANGE paused paused TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('update user_subscriptions set canceled = 0 where canceled is null');
        $this->addSql('ALTER TABLE user_subscriptions CHANGE canceled canceled TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {

        $this->addSql("ALTER TABLE client_stripe CHANGE paused paused TINYINT(1) DEFAULT 0");
        $this->addSql("ALTER TABLE user_subscriptions CHANGE canceled canceled TINYINT(1) DEFAULT 0");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
