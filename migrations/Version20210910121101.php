<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210910121101 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("update users set `name`='' where name is null");
        $this->addSql('ALTER TABLE users CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE workout_tracking CHANGE date date DATE NOT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE stripe_connect CHANGE user_id user_id INT NOT NULL, CHANGE amount amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE recipe_types CHANGE recipe_id recipe_id INT UNSIGNED NOT NULL, CHANGE type type INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE recipes_products CHANGE recipe_id recipe_id INT UNSIGNED NOT NULL, CHANGE meal_product_id meal_product_id INT UNSIGNED NOT NULL, CHANGE weight_units weight_units DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE recipe_preferences CHANGE recipe_id recipe_id INT UNSIGNED NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('delete from recipes_meta where recipe_id is null');
        $this->addSql('ALTER TABLE recipes_meta CHANGE recipe_id recipe_id INT UNSIGNED NOT NULL');
        $this->addSql('update recipes set cooking_time = 0 where cooking_time is null');
        $this->addSql('ALTER TABLE recipes CHANGE type type INT DEFAULT 0 NOT NULL, CHANGE macro_split macro_split INT NOT NULL, CHANGE cooking_time cooking_time INT NOT NULL');
        $this->addSql('update queue set created_at = NOW() where created_at is null;');
        $this->addSql('ALTER TABLE queue DROP parameters');
        $this->addSql("update queue set name='' where name is null");
        $this->addSql("update queue set email='' where email is null");
        $this->addSql('ALTER TABLE queue CHANGE name name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE questions CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE push_messages CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE progress_feedbacks CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE plans CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE payment_id payment_id INT UNSIGNED NOT NULL, CHANGE bundle_id bundle_id INT UNSIGNED NOT NULL');
        $this->addSql("update payments set datakey='' where datakey is null");
        $this->addSql("update payments set months=0 where months is null");
        $this->addSql("update payments set recurring_fee=0 where recurring_fee is null");
        $this->addSql("update payments set currency='usd' where currency is null");
        $this->addSql('ALTER TABLE payments CHANGE recurring_fee recurring_fee VARCHAR(50) NOT NULL, CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE datakey datakey VARCHAR(255) NOT NULL, CHANGE months months INT NOT NULL, CHANGE currency currency VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE news CHANGE picture picture VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE meal_products_weights CHANGE product_id product_id INT UNSIGNED NOT NULL, CHANGE weight weight DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE meal_product_meta CHANGE meal_product_id meal_product_id INT UNSIGNED NOT NULL');
        $this->addSql('delete from meal_plans_products where meal_plan_id is null and meal_product_id is null');
        $this->addSql('ALTER TABLE meal_plans_products CHANGE meal_product_id meal_product_id INT UNSIGNED NOT NULL, CHANGE weight_units weight_units DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql("update meal_plans set created_at = '2016-01-01 00:00:00' where created_at is null");
        $this->addSql("update meal_plans set type=0 where type is null");
        $this->addSql('ALTER TABLE meal_plans CHANGE master_meal_plan_id master_meal_plan_id INT UNSIGNED NOT NULL, CHANGE `order` `order` SMALLINT UNSIGNED DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE type type INT DEFAULT 0 NOT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE contains_alternatives contains_alternatives TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE percent_weight percent_weight DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE master_meal_plan_meta CHANGE plan_id plan_id INT UNSIGNED NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE lead_tags CHANGE lead_id lead_id INT UNSIGNED NOT NULL');
        $this->addSql('update master_meal_plans set created_at = NOW() where created_at is null;');
        $this->addSql('update master_meal_plans set last_updated = NOW() where last_updated is null;');
        $this->addSql('ALTER TABLE master_meal_plans CHANGE last_updated last_updated DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
//        $this->addSql('ALTER TABLE master_meal_plans CHANGE user_id user_id INT UNSIGNED NOT NULL');??
        $this->addSql('ALTER TABLE landing_pages CHANGE user_id user_id INT NOT NULL, CHANGE slug slug VARCHAR(100) NOT NULL, CHANGE background_image background_image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE body_progress CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE weight weight DOUBLE PRECISION DEFAULT NULL, CHANGE fat fat DOUBLE PRECISION DEFAULT NULL, CHANGE date date DATETIME NOT NULL, CHANGE chest chest DOUBLE PRECISION DEFAULT NULL, CHANGE waist waist DOUBLE PRECISION DEFAULT NULL, CHANGE hips hips DOUBLE PRECISION DEFAULT NULL, CHANGE glutes glutes DOUBLE PRECISION DEFAULT NULL, CHANGE left_arm left_arm DOUBLE PRECISION DEFAULT NULL, CHANGE right_arm right_arm DOUBLE PRECISION DEFAULT NULL, CHANGE left_thigh left_thigh DOUBLE PRECISION DEFAULT NULL, CHANGE right_thigh right_thigh DOUBLE PRECISION DEFAULT NULL, CHANGE left_calf left_calf DOUBLE PRECISION DEFAULT NULL, CHANGE right_calf right_calf DOUBLE PRECISION DEFAULT NULL, CHANGE muscle_mass muscle_mass DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('update events set notify_trainer=0 where notify_trainer is null');
        $this->addSql('ALTER TABLE events CHANGE notify_trainer notify_trainer TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE meal_products CHANGE protein protein DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE fat fat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE saturated_fat saturated_fat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE mono_unsaturated_fat mono_unsaturated_fat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE poly_unsaturated_fat poly_unsaturated_fat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE carbohydrates carbohydrates DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE added_sugars added_sugars DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE fiber fiber DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE alcohol alcohol DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE cholesterol cholesterol DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE client_food_preferences CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE client_settings CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE client_status CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE client_stripe CHANGE client_id client_id INT UNSIGNED NOT NULL, CHANGE canceled canceled TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql("update clients set name='' where name is null");
        $this->addSql("update clients set email='' where email is null");
        $this->addSql('delete from clients where user_id is null');
        $this->addSql('ALTER TABLE clients CHANGE user_id user_id INT NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE demo_client demo_client TINYINT(1) NOT NULL, CHANGE lasse_demo_client lasse_demo_client TINYINT(1) NOT NULL, CHANGE accept_terms accept_terms TINYINT(1) NOT NULL, CHANGE accept_email_notifications accept_email_notifications TINYINT(1) NOT NULL');
//        $this->addSql('ALTER TABLE clients CHANGE workout_location workout_location INT DEFAULT NULL'); ??
        $this->addSql('ALTER TABLE activity_log CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE bundle_log CHANGE bundle_id bundle_id INT UNSIGNED NOT NULL');
        $this->addSql("update bundles set type=0 where type is null");
        $this->addSql("update bundles set recurring_fee=0 where recurring_fee is null");
        $this->addSql("update bundles set months=0 where months is null");
        $this->addSql('ALTER TABLE bundles CHANGE user_id user_id INT NOT NULL, CHANGE upfront_fee upfront_fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE currency currency VARCHAR(5) NOT NULL, CHANGE recurring_fee recurring_fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE months months INT DEFAULT 0 NOT NULL, CHANGE type type INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE workout_plans_settings CHANGE plan_id plan_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE workout_plan_meta CHANGE plan_id plan_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE videos CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_tags CHANGE video_id video_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE video_clients CHANGE video_id video_id INT UNSIGNED NOT NULL, CHANGE client_id client_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE user_terms CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_stripe CHANGE fee_percentage fee_percentage DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE user_app CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE messages CHANGE conversation_id conversation_id INT NOT NULL');
        $this->addSql('delete from meal_product_language where meal_product_id is null');
        $this->addSql('ALTER TABLE meal_product_language CHANGE meal_product_id meal_product_id INT UNSIGNED NOT NULL, CHANGE language_id language_id INT NOT NULL');
        $this->addSql('ALTER TABLE leads CHANGE user_id user_id INT NOT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
        $this->addSql('ALTER TABLE documents CHANGE comment comment VARCHAR(255) DEFAULT NULL');
        $this->addSql("update queue set survey=0 where survey is null");
        $this->addSql('ALTER TABLE queue CHANGE survey survey TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipes_meta CHANGE recipe_id recipe_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE name name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE workout_tracking CHANGE date date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE stripe_connect CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE stripe_connect CHANGE user_id user_id INT DEFAULT NULL, CHANGE amount amount NUMERIC(8, 2) NOT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
        $this->addSql('ALTER TABLE recipe_types CHANGE recipe_id recipe_id INT UNSIGNED DEFAULT NULL, CHANGE type type INT DEFAULT 0');
        $this->addSql('ALTER TABLE recipes_products CHANGE recipe_id recipe_id INT UNSIGNED DEFAULT NULL, CHANGE meal_product_id meal_product_id INT UNSIGNED DEFAULT NULL, CHANGE weight_units weight_units NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE recipe_preferences CHANGE recipe_id recipe_id INT UNSIGNED DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipes CHANGE type type INT DEFAULT 0, CHANGE macro_split macro_split INT DEFAULT NULL, CHANGE cooking_time cooking_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE queue CHANGE name name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE email email VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE push_messages CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE progress_feedbacks CHANGE client_id client_id INT UNSIGNED DEFAULT NULL, CHANGE content content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE plans CHANGE client_id client_id INT UNSIGNED DEFAULT NULL, CHANGE payment_id payment_id INT UNSIGNED DEFAULT NULL, CHANGE bundle_id bundle_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE news CHANGE picture picture VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE meal_products_weights CHANGE product_id product_id INT UNSIGNED DEFAULT NULL, CHANGE weight weight NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE meal_product_meta CHANGE meal_product_id meal_product_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE meal_plans_products CHANGE meal_plan_id meal_plan_id INT UNSIGNED DEFAULT NULL, CHANGE meal_product_id meal_product_id INT UNSIGNED DEFAULT NULL, CHANGE weight_units weight_units NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE meal_plans CHANGE master_meal_plan_id master_meal_plan_id INT UNSIGNED DEFAULT NULL, CHANGE `order` `order` SMALLINT UNSIGNED DEFAULT 1, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT \'0\', CHANGE type type INT DEFAULT 0, CHANGE contains_alternatives contains_alternatives TINYINT(1) DEFAULT \'0\', CHANGE percent_weight percent_weight NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE master_meal_plan_meta CHANGE plan_id plan_id INT UNSIGNED DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lead_tags CHANGE lead_id lead_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE leads CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE leads CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE master_meal_plans CHANGE last_updated last_updated DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE landing_pages CHANGE user_id user_id INT NOT NULL, CHANGE slug slug VARCHAR(100) NOT NULL, CHANGE background_image background_image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE body_progress CHANGE client_id client_id INT UNSIGNED DEFAULT NULL, CHANGE weight weight NUMERIC(5, 2) DEFAULT NULL, CHANGE muscle_mass muscle_mass NUMERIC(5, 2) DEFAULT NULL, CHANGE fat fat NUMERIC(5, 2) DEFAULT NULL, CHANGE date date DATETIME DEFAULT NULL, CHANGE chest chest NUMERIC(5, 2) DEFAULT NULL, CHANGE waist waist NUMERIC(5, 2) DEFAULT NULL, CHANGE hips hips NUMERIC(5, 2) DEFAULT NULL, CHANGE glutes glutes NUMERIC(5, 2) DEFAULT NULL, CHANGE left_arm left_arm NUMERIC(5, 2) DEFAULT NULL, CHANGE right_arm right_arm NUMERIC(5, 2) DEFAULT NULL, CHANGE left_thigh left_thigh NUMERIC(5, 2) DEFAULT NULL, CHANGE right_thigh right_thigh NUMERIC(5, 2) DEFAULT NULL, CHANGE left_calf left_calf NUMERIC(5, 2) DEFAULT NULL, CHANGE right_calf right_calf NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE events CHANGE notify_trainer notify_trainer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meal_products CHANGE protein protein NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE fat fat NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE saturated_fat saturated_fat NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE mono_unsaturated_fat mono_unsaturated_fat NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE poly_unsaturated_fat poly_unsaturated_fat NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE carbohydrates carbohydrates NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE added_sugars added_sugars NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE fiber fiber NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE alcohol alcohol NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE cholesterol cholesterol NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE client_food_preferences CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE client_settings CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE client_status CHANGE client_id client_id INT UNSIGNED DEFAULT NULL, CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client_stripe CHANGE client_id client_id INT UNSIGNED DEFAULT NULL, CHANGE canceled canceled TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE clients CHANGE user_id user_id INT DEFAULT NULL, CHANGE name name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE email email VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE demo_client demo_client TINYINT(1) DEFAULT NULL, CHANGE lasse_demo_client lasse_demo_client TINYINT(1) DEFAULT NULL, CHANGE accept_email_notifications accept_email_notifications TINYINT(1) DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
