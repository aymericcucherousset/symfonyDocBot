<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240120182926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE EXTENSION IF NOT EXISTS vector;');
        $this->addSql('CREATE TABLE IF NOT EXISTS embedding (
            id SERIAL PRIMARY KEY,
            content TEXT,
            type TEXT,
            sourcetype TEXT,
            sourcename TEXT,
            embedding VECTOR,
            version TEXT
         );');

        $this->addSql('ALTER TABLE embedding ALTER embedding TYPE vector(1536)');
        $this->addSql('COMMENT ON COLUMN embedding.embedding IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
