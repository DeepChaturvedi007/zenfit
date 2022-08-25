<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210130120648 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lead_tags');
        $this->addSql('ALTER TABLE client_stripe DROP last_payment_warning_date, DROP payment_warning_count');
        $this->addSql('ALTER TABLE clients DROP type, DROP update_plans_schedule');
        $this->addSql('ALTER TABLE meal_plans ADD CONSTRAINT FK_8FAD700759D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payments_log RENAME INDEX fk_e7e6244ea76ed395 TO IDX_E7E6244EA76ED395');
        $this->addSql('ALTER TABLE queue DROP INDEX IDX_7FFD7F634C3A3BB, ADD UNIQUE INDEX UNIQ_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE user_stripe DROP INDEX IDX_C1A3F767A76ED395, ADD UNIQUE INDEX UNIQ_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE users DROP version');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E982F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E982F1BAF4 ON users (language_id)');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead_tags (id INT AUTO_INCREMENT NOT NULL, lead_id INT UNSIGNED DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8E4AD1FC55458D (lead_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE lead_tags ADD CONSTRAINT FK_8E4AD1FC55458D FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_stripe ADD last_payment_warning_date DATETIME DEFAULT NULL, ADD payment_warning_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE clients ADD type INT DEFAULT NULL, ADD update_plans_schedule INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meal_plans DROP FOREIGN KEY FK_8FAD700759D8A214');
        $this->addSql('ALTER TABLE payments_log RENAME INDEX idx_e7e6244ea76ed395 TO FK_E7E6244EA76ED395');
        $this->addSql('ALTER TABLE queue DROP INDEX UNIQ_7FFD7F634C3A3BB, ADD INDEX IDX_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE user_stripe DROP INDEX UNIQ_C1A3F767A76ED395, ADD INDEX IDX_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E982F1BAF4');
        $this->addSql('DROP INDEX IDX_1483A5E982F1BAF4 ON users');
        $this->addSql('ALTER TABLE users ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
