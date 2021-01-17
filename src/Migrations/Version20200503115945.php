<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200503115945 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX `index` ON genres (api_id, genre_id)');
        $this->addSql('CREATE INDEX `index` ON messages (user_id, parent_id, shared_api_id)');
        $this->addSql('CREATE INDEX `index` ON users_movies (relation_type_id, user_id, movie_id, api_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX `index` ON genres');
        $this->addSql('DROP INDEX `index` ON messages');
        $this->addSql('DROP INDEX `index` ON users_movies');
    }
}
