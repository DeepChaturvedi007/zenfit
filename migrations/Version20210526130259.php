<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526130259 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX client_resolved_event ON client_status (client_id, resolved, event_id)');
        $this->addSql('CREATE INDEX user_deleted_active ON clients (user_id, deleted, active)');
        $this->addSql('CREATE INDEX new_deleted_conversation_user_client ON messages (is_new, deleted, conversation_id, client_id, user_id)');
        $this->addSql('CREATE INDEX user_approved_deleted ON recipes (user_id, approved, deleted)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX client_resolved_event ON client_status');
        $this->addSql('DROP INDEX user_deleted_active ON clients');
        $this->addSql('DROP INDEX new_deleted_conversation_user_client ON messages');
        $this->addSql('DROP INDEX user_approved_deleted ON recipes');
    }
}
