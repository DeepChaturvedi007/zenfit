<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202011191216210 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `events` (`id`, `name`, `title`, `notify_trainer`, `priority`)
          VALUES (NULL, 'client.missing_communication', 'No communication in > 7 days', NULL, 8)");
    }

    public function down(Schema $schema) : void
    {
    }
}
