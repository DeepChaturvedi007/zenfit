<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202012061216210 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE subscriptions set currency = 'dkk' where currency is null;");
    }

    public function down(Schema $schema) : void
    {
    }
}
