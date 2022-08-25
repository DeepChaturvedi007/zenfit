<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201027121621 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `default_messages` (`id`, `user_id`, `message`, `type`, `title`, `subject`, `locale`)
VALUES
	(NULL, NULL, 'Hi [client], your last payment failed - please clickc<a href=[invoice]>here</a> to pay the latest invoice.', 8, 'Payment failed', NULL, 'en'),
	(NULL, NULL, 'Hej [client], din sidste betaling fejlede. Venligst klik <a href=[invoice]>her</a> for at betale din regning.', 8, 'Payment failed', NULL, 'da_DK'),
	(NULL, NULL, 'Hi [client], you’ve missed your last check-in. Please check-in asap in the app.', 12, 'Missed Check-in', NULL, 'en'),
	(NULL, NULL, 'Hejsa [client], du mangler at checke-ind. Du må meget gerne få tjekket ind i appen asap.', 12, 'Missed Check-in', NULL, 'da_DK'),
	(NULL, NULL, 'Hi [client], you’re good to go. Looking forward to getting you started!', 13, 'Activate client', NULL, 'en'),
	(NULL, NULL, 'Hejsa [client], du er klar til at komme igang. Glæder mig til at få dig igang!', 13, 'Activate client', NULL, 'da_DK');
");
        #$this->addSql('ALTER TABLE users ADD language_id INT DEFAULT NULL');
        #$this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E982F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E982F1BAF4 ON users (language_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E982F1BAF4');
        $this->addSql('DROP INDEX IDX_1483A5E982F1BAF4 ON users');
        $this->addSql('ALTER TABLE users DROP language_id');
    }
}
