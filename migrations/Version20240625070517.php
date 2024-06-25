<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240625070517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE meal_categorie (meal_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_F712D43F639666D6 (meal_id), INDEX IDX_F712D43FBCF5E72D (categorie_id), PRIMARY KEY(meal_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meal_categorie ADD CONSTRAINT FK_F712D43F639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_categorie ADD CONSTRAINT FK_F712D43FBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meal_categorie DROP FOREIGN KEY FK_F712D43F639666D6');
        $this->addSql('ALTER TABLE meal_categorie DROP FOREIGN KEY FK_F712D43FBCF5E72D');
        $this->addSql('DROP TABLE meal_categorie');
    }
}
