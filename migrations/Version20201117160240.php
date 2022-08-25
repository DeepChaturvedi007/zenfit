<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201117160240 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user_settings ADD mail_chimp_api_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_settings ADD mail_chimp_list_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user_settings DROP mail_chimp_api_key');
        $this->addSql('ALTER TABLE user_settings DROP mail_chimp_list_id');
    }
}
