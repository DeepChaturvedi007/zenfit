<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200817091003 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE email_metrics DROP FOREIGN KEY FK_9F4AC228B403A2B6');
        $this->addSql('ALTER TABLE meal_plans DROP FOREIGN KEY FK_8FAD70075DA0FB8');
        $this->addSql('ALTER TABLE visit_log DROP FOREIGN KEY FK_B72D696970BEE6D');
        $this->addSql('ALTER TABLE workout_template DROP FOREIGN KEY FK_4F11F235DDBD6AB2');
        $this->addSql('ALTER TABLE workout_day_template DROP FOREIGN KEY FK_B321781BFA7625D8');
        $this->addSql('ALTER TABLE workout_plans_template_settings DROP FOREIGN KEY FK_F6A85EF6E899029B');
        $this->addSql('ALTER TABLE workout_template DROP FOREIGN KEY FK_4F11F235727ACA70');
        $this->addSql('DROP TABLE apple_device_token');
        $this->addSql('DROP TABLE automated_emails');
        $this->addSql('DROP TABLE document_client');
        $this->addSql('DROP TABLE email_metrics');
        $this->addSql('DROP TABLE item_log');
        $this->addSql('DROP TABLE meal_templates');
        $this->addSql('DROP TABLE request_log');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE search_text_index');
        $this->addSql('DROP TABLE video_client');
        $this->addSql('DROP TABLE visit_log');
        $this->addSql('DROP TABLE visitors');
        $this->addSql('DROP TABLE workout_day_template');
        $this->addSql('DROP TABLE workout_plans_template');
        $this->addSql('DROP TABLE workout_plans_template_settings');
        $this->addSql('DROP TABLE workout_template');
        $this->addSql('ALTER TABLE body_progress DROP neck'); //!!!!!!!!!
        $this->addSql('ALTER TABLE bundle_log RENAME INDEX fk_fbc079e119eb6921 TO IDX_FBC079E119EB6921'); //!!!!!
        $this->addSql('UPDATE client_images set type=0 where type is null');
        $this->addSql('ALTER TABLE client_images CHANGE type type INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE client_settings DROP INDEX IDX_CBA3900019EB6921, ADD UNIQUE INDEX UNIQ_CBA3900019EB6921 (client_id)');
        $this->addSql('DELETE FROM client_stripe where client_id=8607 and payment_id is null and id=1013');
        $this->addSql('DELETE FROM client_stripe where client_id=8604 and payment_id is null and id=1017');
        $this->addSql('DELETE FROM client_stripe where client_id=8604 and payment_id is null and id=1017');
        $this->addSql('DELETE FROM client_stripe where client_id=5483 and payment_id=9844 and id=6100');
        $this->addSql('DELETE FROM client_stripe where client_id=25989 and payment_id=10029 and id=6199');
        $this->addSql('DELETE FROM client_stripe where client_id=26549 and payment_id=10579 and id=6576');
        $this->addSql('DELETE FROM client_stripe where client_id=30527 and payment_id=12379 and id=7723');
        $this->addSql('DELETE FROM client_stripe where client_id=28615 and payment_id=12380 and id=7724');
        $this->addSql('ALTER TABLE client_stripe DROP INDEX IDX_F157A7E919EB6921, ADD UNIQUE INDEX UNIQ_F157A7E919EB6921 (client_id)');
        $this->addSql('ALTER TABLE client_stripe DROP INDEX FK_F157A7E94C3A3BB, ADD UNIQUE INDEX UNIQ_F157A7E94C3A3BB (payment_id)'); //!!!!!!!!
        $this->addSql('ALTER TABLE client_stripe DROP FOREIGN KEY FK_F157A7E919EB6921');
        $this->addSql('ALTER TABLE client_stripe ADD CONSTRAINT FK_F157A7E919EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE clients DROP primary_goal2, DROP goal_description, DROP trainer_token'); //!!!!
        $this->addSql('ALTER TABLE clients DROP goal, DROP allergies, DROP food_preferences, DROP about, DROP time_week, DROP time_day, DROP hours_sleep, DROP get_started_hidden, DROP type, DROP activated_on, DROP last_queue_status, DROP day_track_macros');
        $this->addSql('CREATE INDEX deleted_idx ON clients (deleted)'); //!!!
        $this->addSql('CREATE INDEX email_idx ON clients (email)'); //!!!
        $this->addSql('ALTER TABLE documents DROP assign_to_all'); //!!!
        $this->addSql('ALTER TABLE documents RENAME INDEX fk_a2b07288f1fad9d3 TO IDX_A2B07288F1FAD9D3'); //!!!
        $this->addSql('ALTER TABLE exercises DROP preparation, DROP source'); //!!!
        $this->addSql('ALTER TABLE leads ADD CONSTRAINT FK_17904552F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES bundles (id) ON DELETE CASCADE'); //!!!
        $this->addSql('ALTER TABLE leads RENAME INDEX fk_17904552f1fad9d3 TO IDX_17904552F1FAD9D3'); //!!!
        $this->addSql("UPDATE master_meal_plans set name='' where name is null");
        $this->addSql('ALTER TABLE master_meal_plans DROP auto_assign'); //!!!
        $this->addSql('ALTER TABLE master_meal_plans DROP template_id, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX IDX_8FAD70075DA0FB8 ON meal_plans');
        $this->addSql('ALTER TABLE meal_plans DROP template_id, DROP locale, CHANGE `order` `order` SMALLINT UNSIGNED DEFAULT 1');
        $this->addSql('UPDATE `meal_plans` mp left join recipes r on mp.recipe_id=r.id  set recipe_id=null where r.id is null and mp.recipe_id is not null');
        $this->addSql('ALTER TABLE meal_plans ADD CONSTRAINT FK_8FAD700759D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('CREATE FULLTEXT INDEX name_idx ON meal_products (name)');
        $this->addSql('ALTER TABLE messages RENAME INDEX fk_db021e9680d7d0b2 TO IDX_DB021E9680D7D0B2'); //!!!
        $this->addSql('ALTER TABLE payments DROP comment'); //!!!
        $this->addSql('ALTER TABLE payments CHANGE sent_at sent_at DATETIME DEFAULT NULL');
        $this->addSql('update plans set type=0 where type is null');
        $this->addSql('ALTER TABLE plans CHANGE type type INT NOT NULL');
        for ($i = 1; $i <= 9; $i++) {
            $this->addSql('delete e.* FROM queue e WHERE e.payment_id is not null and id IN (SELECT id FROM (SELECT MIN(id) as id FROM queue e2 GROUP BY payment_id HAVING COUNT(*) > 1) x);');
        }
        $this->addSql('ALTER TABLE queue DROP INDEX IDX_7FFD7F634C3A3BB, ADD UNIQUE INDEX UNIQ_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE queue CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX name_idx ON recipes (name)');
        $this->addSql('ALTER TABLE recipes RENAME INDEX fk_a369e2b5727aca70 TO IDX_A369E2B5727ACA70');
        $this->addSql('ALTER TABLE stripe_connect CHANGE amount amount NUMERIC(8, 2) DEFAULT NULL');
        $this->addSql("update user_stripe set vat=NULL where vat='';");
        $this->addSql("delete from user_stripe where user_id=1902 and stripe_user_id='acct_1BQzhPFFxykbPfkZ' and vat is null;");
        $this->addSql("delete from user_stripe where user_id=2364 and stripe_user_id='acct_1DTZVEDOzMwbhqzz' and vat is null;");
        $this->addSql("delete from user_stripe where user_id=3383 and stripe_user_id='acct_1Gj79fCD5oNtGVQ7' and vat is null;");

        for ($i = 1; $i <= 4; $i++) {
            $this->addSql('delete e.* FROM user_stripe e WHERE e.user_id is not null and id IN (SELECT id FROM (SELECT MIN(id) as id FROM user_stripe e2 GROUP BY user_id HAVING COUNT(*) > 1) x);');
        }
        $this->addSql('ALTER TABLE user_stripe DROP INDEX IDX_C1A3F767A76ED395, ADD UNIQUE INDEX UNIQ_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_stripe DROP FOREIGN KEY FK_C1A3F767A76ED395');
        $this->addSql('ALTER TABLE user_stripe ADD CONSTRAINT FK_C1A3F767A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9688E3B5D'); //!!!
        $this->addSql('DROP INDEX IDX_1483A5E9688E3B5D ON users'); //!!!
        $this->addSql("delete from user_settings where user_id = 2514 and id=2387");
        $this->addSql("delete from users where id =2514 and username='' and email='' and password=''");
        $this->addSql('ALTER TABLE users DROP subscriptions_id, DROP zenfit_trial, DROP zenfit_trial_expire, DROP zenfit_stripe_active, DROP zenfit_stripe_plan_expire, DROP stripe_customer_id, DROP stripe_subscription_id, DROP created_demo_client, DROP sent_second_welcome_email, DROP admin, DROP stop_showing_modal, DROP check_out_mobile_app_modal, DROP workout_plan_tour, DROP client_overview_tour, DROP sent_welcome_email_after_2_hrs, DROP receive_emails, DROP meal_plan_tour, DROP subscribed_date, DROP subscribe_amount, DROP finished_intro, DROP workout_day_tour, DROP client_workout_day_tour, DROP get_started_tour, DROP stripe_subscription_canceled, DROP default_message, DROP feedback_default_message, DROP payment_message, DROP stripe_subscription_item, CHANGE welcome_message welcome_message VARCHAR(2000) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E99A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E99A1887DC ON users (subscription_id)'); //!!!
        $this->addSql('ALTER TABLE videos DROP assign_to_all'); //!!!
        $this->addSql('ALTER TABLE workout_plan_meta DROP weekly_workouts');
        $this->addSql('update workout_plans set deleted=0 where deleted is null');
        $this->addSql('ALTER TABLE workout_plans DROP auto_assign'); //!!!
        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');


// uncomment these for local
//        $this->addSql('DROP TABLE user_check_in');
//        $this->addSql('ALTER TABLE gyms RENAME INDEX fk_c5ca7615642b8210 TO IDX_C5CA7615642B8210');
//        $this->addSql('DROP INDEX FK_17904552F1FAD9D3 ON leads');
//        $this->addSql('ALTER TABLE payments ADD delay_upfront TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE terms terms LONGTEXT DEFAULT NULL');
//        $this->addSql('ALTER TABLE workout_plans CHANGE deleted deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE apple_device_token (id INT UNSIGNED AUTO_INCREMENT NOT NULL, device_token VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, client_id INT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE automated_emails (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, recipients INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE document_client (document_id INT UNSIGNED NOT NULL, client_id INT UNSIGNED NOT NULL, INDEX IDX_7926B53319EB6921 (client_id), INDEX IDX_7926B533C33F7837 (document_id), PRIMARY KEY(document_id, client_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE email_metrics (id INT UNSIGNED AUTO_INCREMENT NOT NULL, automated_email_id INT UNSIGNED DEFAULT NULL, clicks INT DEFAULT 0 NOT NULL, INDEX IDX_9F4AC228B403A2B6 (automated_email_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE item_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, meal_product_id INT UNSIGNED DEFAULT NULL, exercise_id INT UNSIGNED DEFAULT NULL, count INT NOT NULL, INDEX IDX_682A13F7A76ED395 (user_id), INDEX IDX_682A13F7E934951A (exercise_id), INDEX IDX_682A13F7D2E8D0D0 (meal_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE meal_templates (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, explaination VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, demo TINYINT(1) DEFAULT \'0\' NOT NULL, active TINYINT(1) DEFAULT \'0\' NOT NULL, updated_at DATETIME DEFAULT NULL, locale VARCHAR(10) CHARACTER SET utf8 DEFAULT \'en\' NOT NULL COLLATE `utf8_unicode_ci`, comment VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME DEFAULT NULL, INDEX IDX_D1477F85A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE request_log (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, request LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, response LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, date DATETIME NOT NULL, INDEX IDX_4215298919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sales (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, `key` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE search_text_index (id INT AUTO_INCREMENT NOT NULL, foreign_id INT NOT NULL, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, field VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE video_client (video_id INT UNSIGNED NOT NULL, client_id INT UNSIGNED NOT NULL, INDEX IDX_FF13A49819EB6921 (client_id), INDEX IDX_FF13A49829C1004E (video_id), PRIMARY KEY(video_id, client_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE visit_log (id INT AUTO_INCREMENT NOT NULL, visitor_id INT DEFAULT NULL, landing_page_id INT UNSIGNED DEFAULT NULL, page VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, lifetime INT NOT NULL, created_at INT NOT NULL, INDEX IDX_B72D696970BEE6D (visitor_id), INDEX IDX_B72D6969DF122DC5 (landing_page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE visitors (id INT AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, status INT DEFAULT 1 NOT NULL, client_ip VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at INT NOT NULL, updated_at INT DEFAULT NULL, INDEX IDX_7B74A43F19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workout_day_template (id INT UNSIGNED AUTO_INCREMENT NOT NULL, workout_plan_template_id INT UNSIGNED NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, workout_day_comment LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, `order` INT DEFAULT NULL, INDEX IDX_B321781BFA7625D8 (workout_plan_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workout_plans_template (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, explaination VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, active TINYINT(1) DEFAULT \'0\' NOT NULL, demo INT DEFAULT NULL, last_updated DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_10D114DFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workout_plans_template_settings (id INT UNSIGNED AUTO_INCREMENT NOT NULL, plan_id INT UNSIGNED DEFAULT NULL, sets TINYINT(1) DEFAULT \'1\' NOT NULL, reps TINYINT(1) DEFAULT \'1\' NOT NULL, rest TINYINT(1) DEFAULT \'1\' NOT NULL, weight TINYINT(1) DEFAULT \'1\' NOT NULL, tempo TINYINT(1) DEFAULT \'1\' NOT NULL, rm TINYINT(1) DEFAULT \'1\' NOT NULL, time TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_F6A85EF6E899029B (plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workout_template (id INT UNSIGNED AUTO_INCREMENT NOT NULL, exercise_id INT UNSIGNED NOT NULL, workout_day_template_id INT UNSIGNED NOT NULL, parent_id INT UNSIGNED DEFAULT NULL, info LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, comment LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, order_by INT NOT NULL, time VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, reps VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, rest VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, sets VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, start_weight VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, tempo VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, rm VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_4F11F235DDBD6AB2 (workout_day_template_id), INDEX IDX_4F11F235E934951A (exercise_id), INDEX IDX_4F11F235727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE document_client ADD CONSTRAINT FK_7926B53319EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_client ADD CONSTRAINT FK_7926B533C33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email_metrics ADD CONSTRAINT FK_9F4AC228B403A2B6 FOREIGN KEY (automated_email_id) REFERENCES automated_emails (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_log ADD CONSTRAINT FK_682A13F7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_log ADD CONSTRAINT FK_682A13F7D2E8D0D0 FOREIGN KEY (meal_product_id) REFERENCES meal_products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_log ADD CONSTRAINT FK_682A13F7E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_templates ADD CONSTRAINT FK_D1477F85A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_log ADD CONSTRAINT FK_4215298919EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_client ADD CONSTRAINT FK_FF13A49819EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_client ADD CONSTRAINT FK_FF13A49829C1004E FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE visit_log ADD CONSTRAINT FK_B72D696970BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE visit_log ADD CONSTRAINT FK_B72D6969DF122DC5 FOREIGN KEY (landing_page_id) REFERENCES landing_pages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE visitors ADD CONSTRAINT FK_7B74A43F19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_day_template ADD CONSTRAINT FK_B321781BFA7625D8 FOREIGN KEY (workout_plan_template_id) REFERENCES workout_plans_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_plans_template ADD CONSTRAINT FK_10D114DFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_plans_template_settings ADD CONSTRAINT FK_F6A85EF6E899029B FOREIGN KEY (plan_id) REFERENCES workout_plans_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_template ADD CONSTRAINT FK_4F11F235727ACA70 FOREIGN KEY (parent_id) REFERENCES workout_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_template ADD CONSTRAINT FK_4F11F235DDBD6AB2 FOREIGN KEY (workout_day_template_id) REFERENCES workout_day_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_template ADD CONSTRAINT FK_4F11F235E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE body_progress ADD neck NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE bundle_log RENAME INDEX idx_fbc079e119eb6921 TO FK_FBC079E119EB6921');
        $this->addSql('ALTER TABLE client_images CHANGE type type INT DEFAULT 0');
        $this->addSql('ALTER TABLE client_settings DROP INDEX UNIQ_CBA3900019EB6921, ADD INDEX IDX_CBA3900019EB6921 (client_id)');
        $this->addSql('ALTER TABLE client_stripe DROP INDEX UNIQ_F157A7E919EB6921, ADD INDEX IDX_F157A7E919EB6921 (client_id)');
        $this->addSql('ALTER TABLE client_stripe DROP INDEX UNIQ_F157A7E94C3A3BB, ADD INDEX FK_F157A7E94C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE client_stripe DROP FOREIGN KEY FK_F157A7E919EB6921');
        $this->addSql('ALTER TABLE client_stripe ADD CONSTRAINT FK_F157A7E919EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX deleted_idx ON clients');
        $this->addSql('DROP INDEX email_idx ON clients');
        $this->addSql('ALTER TABLE clients ADD goal LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD allergies LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD food_preferences LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD primary_goal2 INT DEFAULT NULL, ADD about LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD goal_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD time_week LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD time_day LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD hours_sleep LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD get_started_hidden TINYINT(1) DEFAULT \'0\' NOT NULL, ADD type INT DEFAULT NULL, ADD activated_on DATETIME DEFAULT NULL, ADD last_queue_status INT DEFAULT NULL, ADD trainer_token VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD day_track_macros INT DEFAULT NULL');
        $this->addSql('ALTER TABLE documents ADD assign_to_all TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE documents RENAME INDEX idx_a2b07288f1fad9d3 TO FK_A2B07288F1FAD9D3');
        $this->addSql('ALTER TABLE exercises ADD preparation LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD source VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE leads DROP FOREIGN KEY FK_17904552F1FAD9D3');
        $this->addSql('ALTER TABLE leads RENAME INDEX idx_17904552f1fad9d3 TO FK_17904552F1FAD9D3');
        $this->addSql('ALTER TABLE master_meal_plans ADD template_id INT UNSIGNED DEFAULT NULL, ADD auto_assign INT DEFAULT NULL, CHANGE name name VARCHAR(255) CHARACTER SET utf8 DEFAULT \'\' COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE meal_plans DROP FOREIGN KEY FK_8FAD700759D8A214');
        $this->addSql('ALTER TABLE meal_plans ADD template_id INT UNSIGNED DEFAULT NULL, ADD locale VARCHAR(10) CHARACTER SET utf8 DEFAULT \'en\' NOT NULL COLLATE `utf8_unicode_ci`, CHANGE `order` `order` SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE meal_plans ADD CONSTRAINT FK_8FAD70075DA0FB8 FOREIGN KEY (template_id) REFERENCES meal_templates (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8FAD70075DA0FB8 ON meal_plans (template_id)');
        $this->addSql('DROP INDEX name_idx ON meal_products');
        $this->addSql('ALTER TABLE messages RENAME INDEX idx_db021e9680d7d0b2 TO FK_DB021E9680D7D0B2');
        $this->addSql('ALTER TABLE payments ADD comment LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE sent_at sent_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE plans CHANGE type type INT DEFAULT NULL');
        $this->addSql('ALTER TABLE queue DROP INDEX UNIQ_7FFD7F634C3A3BB, ADD INDEX IDX_7FFD7F634C3A3BB (payment_id)');
        $this->addSql('ALTER TABLE queue CHANGE name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE email email VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('DROP INDEX name_idx ON recipes');
        $this->addSql('ALTER TABLE recipes RENAME INDEX idx_a369e2b5727aca70 TO FK_A369E2B5727ACA70');
        $this->addSql('ALTER TABLE stripe_connect CHANGE amount amount NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_stripe DROP INDEX UNIQ_C1A3F767A76ED395, ADD INDEX IDX_C1A3F767A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_stripe DROP FOREIGN KEY FK_C1A3F767A76ED395');
        $this->addSql('ALTER TABLE user_stripe ADD CONSTRAINT FK_C1A3F767A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E99A1887DC');
        $this->addSql('DROP INDEX IDX_1483A5E99A1887DC ON users');
        $this->addSql('ALTER TABLE users ADD subscriptions_id INT DEFAULT NULL, ADD zenfit_trial TINYINT(1) DEFAULT \'1\' NOT NULL, ADD zenfit_trial_expire DATETIME DEFAULT NULL, ADD zenfit_stripe_active TINYINT(1) DEFAULT \'0\' NOT NULL, ADD zenfit_stripe_plan_expire DATETIME DEFAULT NULL, ADD stripe_customer_id VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD stripe_subscription_id VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD created_demo_client INT DEFAULT NULL, ADD sent_second_welcome_email INT DEFAULT NULL, ADD admin INT DEFAULT NULL, ADD stop_showing_modal INT DEFAULT NULL, ADD check_out_mobile_app_modal INT DEFAULT NULL, ADD workout_plan_tour INT DEFAULT NULL, ADD client_overview_tour INT DEFAULT NULL, ADD sent_welcome_email_after_2_hrs INT DEFAULT NULL, ADD receive_emails TINYINT(1) DEFAULT \'1\', ADD meal_plan_tour INT DEFAULT NULL, ADD subscribed_date DATETIME DEFAULT NULL, ADD subscribe_amount VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD finished_intro TINYINT(1) DEFAULT NULL, ADD workout_day_tour TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', ADD client_workout_day_tour TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', ADD get_started_tour INT DEFAULT NULL, ADD stripe_subscription_canceled TINYINT(1) DEFAULT \'0\' NOT NULL, ADD default_message LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD feedback_default_message LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD payment_message LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD stripe_subscription_item VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE welcome_message welcome_message LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9688E3B5D FOREIGN KEY (subscriptions_id) REFERENCES subscriptions (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9688E3B5D ON users (subscriptions_id)');
        $this->addSql('ALTER TABLE videos ADD assign_to_all TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE workout_plan_meta ADD weekly_workouts INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workout_plans ADD auto_assign INT DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT NULL');
    }
}
