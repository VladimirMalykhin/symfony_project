<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210204101902 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE format_fonts (id INT AUTO_INCREMENT NOT NULL, font_id INT NOT NULL, title VARCHAR(100) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_6C99AFF6D7F7F9EB (font_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE format_fonts ADD CONSTRAINT FK_6C99AFF6D7F7F9EB FOREIGN KEY (font_id) REFERENCES fonts (id)');
        $this->addSql('ALTER TABLE epacks ADD updater_id INT NOT NULL');
        $this->addSql('ALTER TABLE epacks ADD CONSTRAINT FM_9627A870E37ECFB0 FOREIGN KEY (updater_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9627A870E37ECFB0 ON epacks (updater_id)');
        $this->addSql('ALTER TABLE fonts DROP format, DROP url');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE format_fonts');
        $this->addSql('ALTER TABLE epacks DROP FOREIGN KEY FK_9627A870E37ECFB0');
        $this->addSql('DROP INDEX IDX_9627A870E37ECFB0 ON epacks');
        $this->addSql('ALTER TABLE epacks DROP updater_id');
        $this->addSql('ALTER TABLE fonts ADD format VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
