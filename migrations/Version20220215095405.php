<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215095405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE role_action');
        $this->addSql('ALTER TABLE action CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE cle cle VARCHAR(255) DEFAULT NULL, CHANGE create_at create_at DATETIME DEFAULT NULL, CHANGE update_at update_at DATETIME DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE allocation CHANGE reduction reduction DOUBLE PRECISION DEFAULT NULL, CHANGE createat createat DATE DEFAULT NULL, CHANGE updateat updateat DATE DEFAULT NULL, CHANGE departreel departreel DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE beneficiaire CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE chambre CHANGE surface surface DOUBLE PRECISION DEFAULT NULL, CHANGE photo1 photo1 VARCHAR(255) DEFAULT NULL, CHANGE photo2 photo2 VARCHAR(255) DEFAULT NULL, CHANGE photo3 photo3 VARCHAR(255) DEFAULT NULL, CHANGE photo4 photo4 VARCHAR(255) DEFAULT NULL, CHANGE create_at create_at DATETIME DEFAULT NULL, CHANGE update_at update_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE compte CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE contact_externe_site CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE numero numero VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE demandeaprovisoinment CHANGE datedemande datedemande DATETIME DEFAULT NULL, CHANGE createat createat DATETIME DEFAULT NULL, CHANGE updatedate updatedate DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE demandeitem CHANGE quantite quantite DOUBLE PRECISION DEFAULT NULL, CHANGE createat createat DATETIME DEFAULT NULL, CHANGE updateat updateat DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE entene CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE bp bp VARCHAR(255) DEFAULT NULL, CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE site site VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entreitem CHANGE qt qt DOUBLE PRECISION DEFAULT NULL, CHANGE pu pu DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE fournisseur CHANGE tel1 tel1 VARCHAR(255) DEFAULT NULL, CHANGE tel2 tel2 VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE logo logo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE log CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE action action VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE magasin CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE module CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE create_at create_at DATETIME DEFAULT NULL, CHANGE update_at update_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE parametre CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE site site VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE bp bp VARCHAR(255) DEFAULT NULL, CHANGE pourcentagereservation pourcentagereservation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation CHANGE datedariver datedariver DATE DEFAULT NULL, CHANGE datedepart datedepart DATE DEFAULT NULL, CHANGE heuredariver heuredariver TIME DEFAULT NULL, CHANGE heuredepart heuredepart TIME DEFAULT NULL, CHANGE montan montan DOUBLE PRECISION DEFAULT NULL, CHANGE reduction reduction DOUBLE PRECISION DEFAULT NULL, CHANGE createat createat DATETIME DEFAULT NULL, CHANGE updateat updateat DATETIME DEFAULT NULL, CHANGE etat etat VARCHAR(255) DEFAULT NULL, CHANGE titre titre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE role CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE create_at create_at DATETIME DEFAULT NULL, CHANGE update_at update_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie_financiere CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE sortiritem CHANGE qt qt DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sortirstock CHANGE date date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tarif CHANGE duree duree VARCHAR(255) DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9399E1D33 FOREIGN KEY (antenne_id) REFERENCES entene (id)');
        $this->addSql('CREATE INDEX IDX_E7189C9399E1D33 ON tarif (antenne_id)');
        $this->addSql('ALTER TABLE transaction CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F0B5AF0B FOREIGN KEY (createdby_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D119EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE typechambre CHANGE photo photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE typechambre ADD CONSTRAINT FK_1FED68449B948B2E FOREIGN KEY (antene_id) REFERENCES entene (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE datenaisance datenaisance DATE DEFAULT NULL, CHANGE sexe sexe VARCHAR(255) DEFAULT NULL, CHANGE cni cni VARCHAR(255) DEFAULT NULL, CHANGE lieunaisance lieunaisance VARCHAR(255) DEFAULT NULL, CHANGE etatcivil etatcivil VARCHAR(255) DEFAULT NULL, CHANGE profession profession VARCHAR(255) DEFAULT NULL, CHANGE nationalite nationalite VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE titel titel VARCHAR(255) DEFAULT NULL, CHANGE solde solde DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role_action (role_id INT NOT NULL, action_id INT NOT NULL, INDEX IDX_ECEA6D239D32F035 (action_id), INDEX IDX_ECEA6D23D60322AC (role_id), PRIMARY KEY(role_id, action_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE action CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE cle cle VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE create_at create_at DATETIME DEFAULT \'NULL\', CHANGE update_at update_at DATETIME DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE allocation CHANGE reduction reduction DOUBLE PRECISION DEFAULT \'NULL\', CHANGE createat createat DATE DEFAULT \'NULL\', CHANGE updateat updateat DATE DEFAULT \'NULL\', CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE departreel departreel DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE beneficiaire CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE chambre CHANGE numero numero VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE surface surface DOUBLE PRECISION DEFAULT \'NULL\', CHANGE photo1 photo1 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE photo2 photo2 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE photo3 photo3 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE photo4 photo4 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE create_at create_at DATETIME DEFAULT \'NULL\', CHANGE update_at update_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE compte CHANGE code code VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE intitule intitule VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE contact_externe_site CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE numero numero VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE demandeaprovisoinment CHANGE datedemande datedemande DATETIME DEFAULT \'NULL\', CHANGE createat createat DATETIME DEFAULT \'NULL\', CHANGE updatedate updatedate DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE demandeitem CHANGE quantite quantite DOUBLE PRECISION DEFAULT \'NULL\', CHANGE createat createat DATETIME DEFAULT \'NULL\', CHANGE updateat updateat DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE entene CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE localisation localisation VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE tel tel VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE bp bp VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE site site VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE photo photo VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE acronym acronym VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE entreitem CHANGE qt qt DOUBLE PRECISION DEFAULT \'NULL\', CHANGE pu pu DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE fournisseur CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE tel1 tel1 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE tel2 tel2 VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE localisation localisation VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE log CHANGE description description LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE action action VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE magasin CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE module CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE create_at create_at DATETIME DEFAULT \'NULL\', CHANGE update_at update_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE note CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE parametre CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE site site VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE bp bp VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE pourcentagereservation pourcentagereservation VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE logo logo VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE photo photo LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reservation CHANGE datedariver datedariver DATE DEFAULT \'NULL\', CHANGE datedepart datedepart DATE DEFAULT \'NULL\', CHANGE heuredariver heuredariver TIME DEFAULT \'NULL\', CHANGE heuredepart heuredepart TIME DEFAULT \'NULL\', CHANGE montan montan DOUBLE PRECISION DEFAULT \'NULL\', CHANGE reduction reduction DOUBLE PRECISION DEFAULT \'NULL\', CHANGE createat createat DATETIME DEFAULT \'NULL\', CHANGE updateat updateat DATETIME DEFAULT \'NULL\', CHANGE etat etat VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE titre titre VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE role CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE create_at create_at DATETIME DEFAULT \'NULL\', CHANGE update_at update_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE sortie_financiere CHANGE code code VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE motif motif VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE sortiritem CHANGE qt qt DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE sortirstock CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE date date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9399E1D33');
        $this->addSql('DROP INDEX IDX_E7189C9399E1D33 ON tarif');
        $this->addSql('ALTER TABLE tarif CHANGE duree duree VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F0B5AF0B');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D119EB6921');
        $this->addSql('ALTER TABLE transaction CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE typechambre DROP FOREIGN KEY FK_1FED68449B948B2E');
        $this->addSql('ALTER TABLE typechambre CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE photo photo VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `user` CHANGE username username VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE datenaisance datenaisance DATE DEFAULT \'NULL\', CHANGE sexe sexe VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE cni cni VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lieunaisance lieunaisance VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE etatcivil etatcivil VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE profession profession VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nationalite nationalite VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE photo photo VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE titel titel VARCHAR(255) DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE solde solde DOUBLE PRECISION DEFAULT \'NULL\'');
    }
}
