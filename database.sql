SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS=0;

--
-- Database: `digiwork-hub`
--

CREATE DATABASE IF NOT EXISTS `digiwork-hub`;
USE `digiwork-hub`;

CREATE TABLE `abon-pack` (
  `id-pack` int(11) NOT NULL,
  `id-abonnement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `abonnement` (
  `id-abonnement` int(11) NOT NULL,
  `id-user` int(11) NOT NULL,
  `date-deb` date NOT NULL,
  `date-fin` date NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admin` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `penom` varchar(20) NOT NULL,
  `code-admin` varchar(8) NOT NULL,
  `ddn` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `commentaire` (
  `id-commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date-commentaire` date NOT NULL,
  `id-publication` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `condidat` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `ddn` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `condidateur` (
  `id_user` int(11) NOT NULL,
  `id-offer` int(11) NOT NULL,
  `cv` varchar(200) NOT NULL,
  `lettre-de-motivation` text NOT NULL,
  `date-envoia` date NOT NULL,
  `staut` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `entreprise` (
  `id-entr` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nom-ent` varchar(30) NOT NULL,
  `adress` varchar(50) NOT NULL,
  `discreption` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `evente` (
  `id_event` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_event` date DEFAULT NULL,
  `heure_event` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `id_organisateur` int(11) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `forums` (
  `id-publication` int(11) NOT NULL,
  `titre` varchar(30) NOT NULL,
  `contenu` text NOT NULL,
  `date-publication` date NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_event` int(11) DEFAULT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `offer` (
  `id-offer` int(11) NOT NULL,
  `titre` varchar(20) NOT NULL,
  `discription` text NOT NULL,
  `competence` varchar(30) NOT NULL,
  `date-limiter` date NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `id-enter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `pack` (
  `id-pack` int(11) NOT NULL AUTO_INCREMENT,
  `nom-pack` varchar(20) NOT NULL,
  `prix` float NOT NULL,
  `duree` date NOT NULL,
  `description` text NOT NULL,
  `nb-proj-max` int(11) NOT NULL,
  `support-prioritaire` varchar(10) NOT NULL,
  PRIMARY KEY (`id-pack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `projet` (
  `id-projet` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `discription` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL,
  `id-user` int(11) NOT NULL,
  `id-offre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sponsor` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `discription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sponsorships` (
  `id-sponser` int(11) NOT NULL,
  `id-projet` int(11) NOT NULL,
  `sponser-nom` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `status` enum('actif','termine','annule','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mdp` varchar(30) NOT NULL,
  `tel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `abon-pack`
  ADD PRIMARY KEY (`id-pack`,`id-abonnement`),
  ADD UNIQUE KEY `id-pack` (`id-pack`,`id-abonnement`),
  ADD KEY `id-abonnement` (`id-abonnement`);

ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id-abonnement`,`id-user`),
  ADD UNIQUE KEY `id-abonnement` (`id-abonnement`),
  ADD KEY `abonnemet_pk` (`id-user`);

ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `id_user` (`id_user`);

ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id-commentaire`),
  ADD UNIQUE KEY `id-publication` (`id-publication`);

ALTER TABLE `condidat`
  ADD UNIQUE KEY `id_user` (`id_user`);

ALTER TABLE `condidateur`
  ADD PRIMARY KEY (`id_user`,`id-offer`),
  ADD UNIQUE KEY `id_user` (`id_user`,`id-offer`),
  ADD KEY `id-offer` (`id-offer`);

ALTER TABLE `entreprise`
  ADD PRIMARY KEY (`id-entr`),
  ADD UNIQUE KEY `id_user` (`id_user`);

ALTER TABLE `evente`
  ADD PRIMARY KEY (`id_event`);

ALTER TABLE `forums`
  ADD PRIMARY KEY (`id-publication`),
  ADD UNIQUE KEY `id_user` (`id_user`);

ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_event` (`id_event`);

ALTER TABLE `offer`
  ADD PRIMARY KEY (`id-offer`),
  ADD UNIQUE KEY `id-enter` (`id-enter`);


ALTER TABLE `projet`
  ADD PRIMARY KEY (`id-projet`),
  ADD UNIQUE KEY `id-user` (`id-user`,`id-offre`),
  ADD KEY `id-offre` (`id-offre`);

ALTER TABLE `sponsor`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `id_user` (`id_user`);

ALTER TABLE `sponsorships`
  ADD PRIMARY KEY (`id-sponser`),
  ADD UNIQUE KEY `id-projet` (`id-projet`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);


ALTER TABLE `evente`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `abon-pack`
  ADD CONSTRAINT `abon-pack_ibfk_2` FOREIGN KEY (`id-abonnement`) REFERENCES `abonnement` (`id-abonnement`);

ALTER TABLE `abonnement`
  ADD CONSTRAINT `abonnemet_pk` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`);

ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

ALTER TABLE `commentaire`
  ADD CONSTRAINT `commonter_fks` FOREIGN KEY (`id-publication`) REFERENCES `forums` (`id-publication`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `condidat`
  ADD CONSTRAINT `condidat_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

ALTER TABLE `condidateur`
  ADD CONSTRAINT `condidateur_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `condidateur_ibfk_2` FOREIGN KEY (`id-offer`) REFERENCES `offer` (`id-offer`);

ALTER TABLE `entreprise`
  ADD CONSTRAINT `entreprise_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

ALTER TABLE `forums`
  ADD CONSTRAINT `forums_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`);

ALTER TABLE `offer`
  ADD CONSTRAINT `offer_ibfk_1` FOREIGN KEY (`id-enter`) REFERENCES `entreprise` (`id-entr`);

ALTER TABLE `projet`
  ADD CONSTRAINT `projet_ibfk_1` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `projet_ibfk_2` FOREIGN KEY (`id-offre`) REFERENCES `offer` (`id-offer`);

ALTER TABLE `sponsor`
  ADD CONSTRAINT `sponsor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `admin` (`id_user`);

ALTER TABLE `sponsorships`
  ADD CONSTRAINT `sponsorships_ibfk_1` FOREIGN KEY (`id-projet`) REFERENCES `projet` (`id-projet`);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
