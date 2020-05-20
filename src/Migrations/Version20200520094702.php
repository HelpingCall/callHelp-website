<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200520094702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE invitation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, housenumber VARCHAR(10) NOT NULL, street VARCHAR(255) NOT NULL, telephonenumber VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , token VARCHAR(255) DEFAULT NULL, verified_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, city VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11D61A2E7927C74 ON invitation (email)');
        $this->addSql('DROP INDEX IDX_87377BB058E0A285');
        $this->addSql('CREATE TEMPORARY TABLE __temp__helper AS SELECT id, userid_id, firstname, lastname, email FROM helper');
        $this->addSql('DROP TABLE helper');
        $this->addSql('CREATE TABLE helper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, userid_id INTEGER NOT NULL, firstname VARCHAR(255) NOT NULL COLLATE BINARY, lastname VARCHAR(255) NOT NULL COLLATE BINARY, email VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_87377BB058E0A285 FOREIGN KEY (userid_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO helper (id, userid_id, firstname, lastname, email) SELECT id, userid_id, firstname, lastname, email FROM __temp__helper');
        $this->addSql('DROP TABLE __temp__helper');
        $this->addSql('CREATE INDEX IDX_87377BB058E0A285 ON helper (userid_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP INDEX IDX_87377BB058E0A285');
        $this->addSql('CREATE TEMPORARY TABLE __temp__helper AS SELECT id, userid_id, firstname, lastname, email FROM helper');
        $this->addSql('DROP TABLE helper');
        $this->addSql('CREATE TABLE helper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, userid_id INTEGER NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO helper (id, userid_id, firstname, lastname, email) SELECT id, userid_id, firstname, lastname, email FROM __temp__helper');
        $this->addSql('DROP TABLE __temp__helper');
        $this->addSql('CREATE INDEX IDX_87377BB058E0A285 ON helper (userid_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
