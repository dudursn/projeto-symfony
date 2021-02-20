<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200322155250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE candidatos (id INT AUTO_INCREMENT NOT NULL, criador_id INT NOT NULL, editor_id INT DEFAULT NULL, eleicao_id INT NOT NULL, usuario_id INT NOT NULL, criacao DATETIME NOT NULL, edicao DATETIME DEFAULT NULL, apelido VARCHAR(255) NOT NULL, numero VARCHAR(255) DEFAULT NULL, info LONGTEXT DEFAULT NULL, votos_qtd INT DEFAULT NULL, INDEX IDX_90981086355B1972 (criador_id), INDEX IDX_909810866995AC4C (editor_id), INDEX IDX_909810863841AA65 (eleicao_id), INDEX IDX_90981086DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleicoes (id INT AUTO_INCREMENT NOT NULL, criador_id INT NOT NULL, editor_id INT DEFAULT NULL, criacao DATETIME NOT NULL, edicao DATETIME DEFAULT NULL, ano VARCHAR(255) NOT NULL, descricao VARCHAR(255) DEFAULT NULL, votacao_inicio DATE NOT NULL, votacao_fim DATE NOT NULL, apuracao_data DATE NOT NULL, votos_qtd INT DEFAULT NULL, INDEX IDX_C6BAB766355B1972 (criador_id), INDEX IDX_C6BAB7666995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logins (id INT AUTO_INCREMENT NOT NULL, criador_id INT DEFAULT NULL, editor_id INT DEFAULT NULL, criacao DATETIME NOT NULL, edicao DATETIME DEFAULT NULL, email VARCHAR(255) NOT NULL, pass VARCHAR(255) NOT NULL, nome VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, hash VARCHAR(255) DEFAULT NULL, hash_criacao DATETIME DEFAULT NULL, confirmado TINYINT(1) DEFAULT NULL, ativo TINYINT(1) DEFAULT NULL, INDEX IDX_613D7A4355B1972 (criador_id), INDEX IDX_613D7A46995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, criador_id INT NOT NULL, editor_id INT DEFAULT NULL, criacao DATETIME NOT NULL, edicao DATETIME DEFAULT NULL, nome VARCHAR(255) NOT NULL, doc VARCHAR(255) NOT NULL, cargo VARCHAR(255) DEFAULT NULL, rg VARCHAR(255) DEFAULT NULL, matricula VARCHAR(255) DEFAULT NULL, nascimento DATE DEFAULT NULL, sexo SMALLINT DEFAULT NULL, logradouro VARCHAR(255) DEFAULT NULL, enumero VARCHAR(255) DEFAULT NULL, complemento VARCHAR(255) DEFAULT NULL, bairro VARCHAR(255) NOT NULL, uf VARCHAR(255) DEFAULT NULL, cidade VARCHAR(255) DEFAULT NULL, cep VARCHAR(255) DEFAULT NULL, telefone VARCHAR(255) DEFAULT NULL, whatsapp VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_EF687F2355B1972 (criador_id), INDEX IDX_EF687F26995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE votos (id INT AUTO_INCREMENT NOT NULL, criador_id INT NOT NULL, editor_id INT DEFAULT NULL, eleicao_id INT NOT NULL, usuario_id INT NOT NULL, candidato_id INT NOT NULL, criacao DATETIME NOT NULL, edicao DATETIME DEFAULT NULL, INDEX IDX_AB649245355B1972 (criador_id), INDEX IDX_AB6492456995AC4C (editor_id), INDEX IDX_AB6492453841AA65 (eleicao_id), INDEX IDX_AB649245DB38439E (usuario_id), INDEX IDX_AB649245FE0067E5 (candidato_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidatos ADD CONSTRAINT FK_90981086355B1972 FOREIGN KEY (criador_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE candidatos ADD CONSTRAINT FK_909810866995AC4C FOREIGN KEY (editor_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE candidatos ADD CONSTRAINT FK_909810863841AA65 FOREIGN KEY (eleicao_id) REFERENCES eleicoes (id)');
        $this->addSql('ALTER TABLE candidatos ADD CONSTRAINT FK_90981086DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE eleicoes ADD CONSTRAINT FK_C6BAB766355B1972 FOREIGN KEY (criador_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE eleicoes ADD CONSTRAINT FK_C6BAB7666995AC4C FOREIGN KEY (editor_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE logins ADD CONSTRAINT FK_613D7A4355B1972 FOREIGN KEY (criador_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE logins ADD CONSTRAINT FK_613D7A46995AC4C FOREIGN KEY (editor_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE usuarios ADD CONSTRAINT FK_EF687F2355B1972 FOREIGN KEY (criador_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE usuarios ADD CONSTRAINT FK_EF687F26995AC4C FOREIGN KEY (editor_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE votos ADD CONSTRAINT FK_AB649245355B1972 FOREIGN KEY (criador_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE votos ADD CONSTRAINT FK_AB6492456995AC4C FOREIGN KEY (editor_id) REFERENCES logins (id)');
        $this->addSql('ALTER TABLE votos ADD CONSTRAINT FK_AB6492453841AA65 FOREIGN KEY (eleicao_id) REFERENCES eleicoes (id)');
        $this->addSql('ALTER TABLE votos ADD CONSTRAINT FK_AB649245DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE votos ADD CONSTRAINT FK_AB649245FE0067E5 FOREIGN KEY (candidato_id) REFERENCES candidatos (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE votos DROP FOREIGN KEY FK_AB649245FE0067E5');
        $this->addSql('ALTER TABLE candidatos DROP FOREIGN KEY FK_909810863841AA65');
        $this->addSql('ALTER TABLE votos DROP FOREIGN KEY FK_AB6492453841AA65');
        $this->addSql('ALTER TABLE candidatos DROP FOREIGN KEY FK_90981086355B1972');
        $this->addSql('ALTER TABLE candidatos DROP FOREIGN KEY FK_909810866995AC4C');
        $this->addSql('ALTER TABLE eleicoes DROP FOREIGN KEY FK_C6BAB766355B1972');
        $this->addSql('ALTER TABLE eleicoes DROP FOREIGN KEY FK_C6BAB7666995AC4C');
        $this->addSql('ALTER TABLE logins DROP FOREIGN KEY FK_613D7A4355B1972');
        $this->addSql('ALTER TABLE logins DROP FOREIGN KEY FK_613D7A46995AC4C');
        $this->addSql('ALTER TABLE usuarios DROP FOREIGN KEY FK_EF687F2355B1972');
        $this->addSql('ALTER TABLE usuarios DROP FOREIGN KEY FK_EF687F26995AC4C');
        $this->addSql('ALTER TABLE votos DROP FOREIGN KEY FK_AB649245355B1972');
        $this->addSql('ALTER TABLE votos DROP FOREIGN KEY FK_AB6492456995AC4C');
        $this->addSql('ALTER TABLE candidatos DROP FOREIGN KEY FK_90981086DB38439E');
        $this->addSql('ALTER TABLE votos DROP FOREIGN KEY FK_AB649245DB38439E');
        $this->addSql('DROP TABLE candidatos');
        $this->addSql('DROP TABLE eleicoes');
        $this->addSql('DROP TABLE logins');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE votos');
    }
}
