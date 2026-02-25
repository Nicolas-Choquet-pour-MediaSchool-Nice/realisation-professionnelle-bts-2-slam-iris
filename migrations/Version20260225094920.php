<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225094920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment_room (equipment_id INT NOT NULL, room_id INT NOT NULL, PRIMARY KEY (equipment_id, room_id))');
        $this->addSql('CREATE INDEX IDX_481B809D517FE9FE ON equipment_room (equipment_id)');
        $this->addSql('CREATE INDEX IDX_481B809D54177093 ON equipment_room (room_id)');
        $this->addSql('ALTER TABLE equipment_room ADD CONSTRAINT FK_481B809D517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment_room ADD CONSTRAINT FK_481B809D54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment DROP CONSTRAINT fk_d338d58354177093');
        $this->addSql('DROP INDEX idx_d338d58354177093');
        $this->addSql('ALTER TABLE equipment DROP room_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_room DROP CONSTRAINT FK_481B809D517FE9FE');
        $this->addSql('ALTER TABLE equipment_room DROP CONSTRAINT FK_481B809D54177093');
        $this->addSql('DROP TABLE equipment_room');
        $this->addSql('ALTER TABLE equipment ADD room_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT fk_d338d58354177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d338d58354177093 ON equipment (room_id)');
    }
}
