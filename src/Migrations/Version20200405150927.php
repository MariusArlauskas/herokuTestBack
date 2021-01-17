<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200405150927 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE api_keys (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, api_key VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movies (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) DEFAULT NULL, release_date DATE DEFAULT NULL, overview LONGTEXT DEFAULT NULL, poster_path LONGTEXT DEFAULT NULL, original_title VARCHAR(255) NOT NULL, rating DOUBLE PRECISION NOT NULL, api_id INT NOT NULL, movie_id INT NOT NULL, genres LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX search_idx (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, profile_picture LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, birth_date DATE NOT NULL, register_date DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_movies (id INT AUTO_INCREMENT NOT NULL, relation_type INT DEFAULT NULL, user_id INT NOT NULL, movie_id INT NOT NULL, api_id INT NOT NULL, is_favorite TINYINT(1) NOT NULL, INDEX search_idx (relation_type, user_id, movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_movies_relation_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE api_keys');
        $this->addSql('DROP TABLE movies');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_movies');
        $this->addSql('DROP TABLE user_movies_relation_types');
    }
}
