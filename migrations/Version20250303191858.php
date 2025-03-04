<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303191858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commantaire (id INT AUTO_INCREMENT NOT NULL, id_pub_id INT NOT NULL, user_id INT NOT NULL, contenu LONGTEXT NOT NULL, likes INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_93BF4CAFA5CA559A (id_pub_id), INDEX IDX_93BF4CAFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commantaire ADD CONSTRAINT FK_93BF4CAFA5CA559A FOREIGN KEY (id_pub_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE commantaire ADD CONSTRAINT FK_93BF4CAFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC38B217A7');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('ALTER TABLE publication CHANGE image image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, user_id INT NOT NULL, contenu LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, likes INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_67F068BC38B217A7 (publication_id), INDEX IDX_67F068BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE commantaire DROP FOREIGN KEY FK_93BF4CAFA5CA559A');
        $this->addSql('ALTER TABLE commantaire DROP FOREIGN KEY FK_93BF4CAFA76ED395');
        $this->addSql('DROP TABLE commantaire');
        $this->addSql('ALTER TABLE publication CHANGE image image VARCHAR(10000) DEFAULT NULL');
    }
}
