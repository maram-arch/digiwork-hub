-- phpMyAdmin SQL Dump

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database
CREATE DATABASE IF NOT EXISTS digiwork_hub;
USE digiwork_hub;

-- --------------------------------------------------------

CREATE TABLE `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `mdp` varchar(30) NOT NULL,
  `tel` int NOT NULL,
  PRIMARY KEY (`id_user`)
);

-- --------------------------------------------------------

CREATE TABLE `forums` (
  `id_publication` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(30) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` date NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id_publication`)
);

-- --------------------------------------------------------

CREATE TABLE `commentaire` (
  `id_commentaire` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_commentaire` date NOT NULL,
  `id_publication` int NOT NULL,
  PRIMARY KEY (`id_commentaire`)
);

-- --------------------------------------------------------

CREATE TABLE `abonnement` (
  `id_abonnement` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `date_deb` date NOT NULL,
  `date_fin` date NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id_abonnement`)
);

-- --------------------------------------------------------

CREATE TABLE `pack` (
  `id_pack` int NOT NULL AUTO_INCREMENT,
  `nom_pack` varchar(20) NOT NULL,
  `prix` float NOT NULL,
  `duree` date NOT NULL,
  `description` text NOT NULL,
  `nb_proj_max` int NOT NULL,
  `support_prioritaire` varchar(10) NOT NULL,
  PRIMARY KEY (`id_pack`)
);

-- --------------------------------------------------------

CREATE TABLE `abon_pack` (
  `id_pack` int NOT NULL,
  `id_abonnement` int NOT NULL,
  PRIMARY KEY (`id_pack`, `id_abonnement`)
);

-- --------------------------------------------------------

CREATE TABLE `admin` (
  `id_user` int NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `code_admin` varchar(8) NOT NULL,
  `ddn` date NOT NULL,
  PRIMARY KEY (`id_user`)
);

-- --------------------------------------------------------

CREATE TABLE `entreprise` (
  `id_entr` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `nom_ent` varchar(30) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id_entr`)
);

-- --------------------------------------------------------

CREATE TABLE `offer` (
  `id_offer` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `competence` varchar(30) NOT NULL,
  `date_limite` date NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `id_entr` int NOT NULL,
  PRIMARY KEY (`id_offer`)
);

-- --------------------------------------------------------

CREATE TABLE `projet` (
  `id_projet` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL,
  `id_user` int NOT NULL,
  `id_offer` int NOT NULL,
  PRIMARY KEY (`id_projet`)
);

-- --------------------------------------------------------

CREATE TABLE `evente` (
  `id_event` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255),
  `description` text,
  `date_event` date,
  `heure_event` time,
  `lieu` varchar(255),
  `capacite` int,
  `id_organisateur` int,
  PRIMARY KEY (`id_event`)
);

-- --------------------------------------------------------

CREATE TABLE `inscription` (
  `id_inscription` int NOT NULL AUTO_INCREMENT,
  `id_user` int,
  `id_event` int,
  `date_inscription` timestamp DEFAULT CURRENT_TIMESTAMP,
  `statut` varchar(50),
  PRIMARY KEY (`id_inscription`)
);

-- --------------------------------------------------------

-- FOREIGN KEYS

ALTER TABLE `forums`
  ADD CONSTRAINT `forums_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_fk_forum`
  FOREIGN KEY (`id_publication`) REFERENCES `forums`(`id_publication`)
  ON DELETE CASCADE;

ALTER TABLE `abonnement`
  ADD CONSTRAINT `abonnement_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `abon_pack`
  ADD CONSTRAINT `abon_pack_fk_pack`
  FOREIGN KEY (`id_pack`) REFERENCES `pack`(`id_pack`);

ALTER TABLE `abon_pack`
  ADD CONSTRAINT `abon_pack_fk_abonnement`
  FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement`(`id_abonnement`);

ALTER TABLE `admin`
  ADD CONSTRAINT `admin_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `entreprise`
  ADD CONSTRAINT `entreprise_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `offer`
  ADD CONSTRAINT `offer_fk_entreprise`
  FOREIGN KEY (`id_entr`) REFERENCES `entreprise`(`id_entr`);

ALTER TABLE `projet`
  ADD CONSTRAINT `projet_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `projet`
  ADD CONSTRAINT `projet_fk_offer`
  FOREIGN KEY (`id_offer`) REFERENCES `offer`(`id_offer`);

ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_fk_user`
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`);

ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_fk_event`
  FOREIGN KEY (`id_event`) REFERENCES `evente`(`id_event`);

COMMIT;