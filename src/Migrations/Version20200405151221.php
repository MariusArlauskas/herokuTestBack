<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200405151221 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX search_idx ON user_movies');
        $this->addSql('ALTER TABLE user_movies CHANGE relation_type relation_type_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX search_idx ON user_movies (relation_type_id, user_id, movie_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX search_idx ON user_movies');
        $this->addSql('ALTER TABLE user_movies CHANGE relation_type_id relation_type INT DEFAULT NULL');
        $this->addSql('CREATE INDEX search_idx ON user_movies (relation_type, user_id, movie_id)');
    }
}
