<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212155948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commantaire ADD id_pub_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commantaire ADD CONSTRAINT FK_93BF4CAFA5CA559A FOREIGN KEY (id_pub_id) REFERENCES publication (id)');
        $this->addSql('CREATE INDEX IDX_93BF4CAFA5CA559A ON commantaire (id_pub_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commantaire DROP FOREIGN KEY FK_93BF4CAFA5CA559A');
        $this->addSql('DROP INDEX IDX_93BF4CAFA5CA559A ON commantaire');
        $this->addSql('ALTER TABLE commantaire DROP id_pub_id');
    }
}
