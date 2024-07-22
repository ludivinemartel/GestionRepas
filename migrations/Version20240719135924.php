<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240719135924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870FC0F004');
        $this->addSql('DROP INDEX IDX_6BAF7870FC0F004 ON ingredient');
        $this->addSql('ALTER TABLE ingredient CHANGE food_composition_id_id food_composition_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870662BE1D9 FOREIGN KEY (food_composition_id) REFERENCES food_composition (id)');
        $this->addSql('CREATE INDEX IDX_6BAF7870662BE1D9 ON ingredient (food_composition_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870662BE1D9');
        $this->addSql('DROP INDEX IDX_6BAF7870662BE1D9 ON ingredient');
        $this->addSql('ALTER TABLE ingredient CHANGE food_composition_id food_composition_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870FC0F004 FOREIGN KEY (food_composition_id_id) REFERENCES food_composition (id)');
        $this->addSql('CREATE INDEX IDX_6BAF7870FC0F004 ON ingredient (food_composition_id_id)');
    }
}
