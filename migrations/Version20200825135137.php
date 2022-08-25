<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200825135137 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE leads SET viewed = 0 WHERE viewed IS NULL');
        $this->addSql('ALTER TABLE leads CHANGE viewed viewed INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE master_meal_plans CHANGE name name VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_signup_links (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, user_id INT DEFAULT NULL, random_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, upfront_fee VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, redeemed TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_C66EA7139A1887DC (subscription_id), INDEX IDX_C66EA713A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_signup_links ADD CONSTRAINT FK_C66EA7139A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
        $this->addSql('ALTER TABLE user_signup_links ADD CONSTRAINT FK_C66EA713A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE leads CHANGE viewed viewed INT DEFAULT NULL');
        $this->addSql('ALTER TABLE master_meal_plans CHANGE name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE user_subscriptions ADD user_signup_link_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_subscriptions ADD CONSTRAINT FK_EAF92751A7336963 FOREIGN KEY (user_signup_link_id) REFERENCES user_signup_links (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAF92751A7336963 ON user_subscriptions (user_signup_link_id)');
        $this->addSql('ALTER TABLE users ADD company_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD profile_picture VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD trainer_video VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD trainer_video_url VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD welcome_message VARCHAR(2000) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD company_logo VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD company_email VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD saas TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }
}
