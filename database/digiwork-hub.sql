-- DigiWork Hub merged schema (Phase 2)
-- Auth/user foundation preserved and expanded for business modules.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- =========================================
-- CORE AUTH TABLES (SOURCE OF TRUTH)
-- =========================================
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` enum('condidat','admin','entreprise','sponsor') NOT NULL DEFAULT 'condidat',
  `tel` varchar(20) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `last_activity` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `context` varchar(20) NOT NULL DEFAULT 'signup',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_otp_user` (`id_user`),
  KEY `idx_otp_user_context` (`id_user`,`context`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- BUSINESS TABLES
-- =========================================
CREATE TABLE `admin` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `penom` varchar(20) NOT NULL,
  `code-admin` varchar(8) NOT NULL,
  `ddn` date NOT NULL,
  PRIMARY KEY (`id_user`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `condidat` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `ddn` date NOT NULL,
  PRIMARY KEY (`id_user`),
  CONSTRAINT `condidat_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `entreprise` (
  `id-entr` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nom-ent` varchar(30) NOT NULL,
  `adress` varchar(50) NOT NULL,
  `discreption` varchar(200) NOT NULL,
  PRIMARY KEY (`id-entr`),
  UNIQUE KEY `uq_entreprise_user` (`id_user`),
  CONSTRAINT `entreprise_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `offer` (
  `id-offer` int(11) NOT NULL,
  `titre` varchar(20) NOT NULL,
  `discription` text NOT NULL,
  `competence` varchar(30) NOT NULL,
  `date-limiter` date NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `id-enter` int(11) NOT NULL,
  PRIMARY KEY (`id-offer`),
  KEY `idx_offer_enter` (`id-enter`),
  CONSTRAINT `offer_ibfk_1` FOREIGN KEY (`id-enter`) REFERENCES `entreprise` (`id-entr`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pack` (
  `id-pack` int(11) NOT NULL AUTO_INCREMENT,
  `nom-pack` varchar(20) NOT NULL,
  `prix` float NOT NULL,
  `duree` date NOT NULL,
  `description` text NOT NULL,
  `nb-proj-max` int(11) NOT NULL,
  `support-prioritaire` varchar(10) NOT NULL,
  PRIMARY KEY (`id-pack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `abonnement` (
  `id-abonnement` int(11) NOT NULL AUTO_INCREMENT,
  `id-user` int(11) NOT NULL,
  `date-deb` date NOT NULL,
  `date-fin` date NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id-abonnement`),
  KEY `idx_abonnement_user` (`id-user`),
  CONSTRAINT `abonnemet_pk` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `abon-pack` (
  `id-pack` int(11) NOT NULL,
  `id-abonnement` int(11) NOT NULL,
  PRIMARY KEY (`id-pack`,`id-abonnement`),
  KEY `idx_abon_pack_abonnement` (`id-abonnement`),
  CONSTRAINT `abon-pack_ibfk_1` FOREIGN KEY (`id-pack`) REFERENCES `pack` (`id-pack`) ON DELETE CASCADE,
  CONSTRAINT `abon-pack_ibfk_2` FOREIGN KEY (`id-abonnement`) REFERENCES `abonnement` (`id-abonnement`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evente` (
  `id_event` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_event` date DEFAULT NULL,
  `heure_event` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `id_organisateur` int(11) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `nbr_inscri` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_event`),
  KEY `idx_event_user` (`id_organisateur`),
  CONSTRAINT `fk_event_user` FOREIGN KEY (`id_organisateur`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pack_event` (
  `id_pack_event` int(11) NOT NULL AUTO_INCREMENT,
  `id_pack` int(11) NOT NULL,
  `id_event` int(11) NOT NULL,
  `statut` enum('actif','inactif','en_attente') NOT NULL DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pack_event`),
  UNIQUE KEY `uq_pack_event` (`id_pack`,`id_event`),
  KEY `idx_pack_event_event` (`id_event`),
  CONSTRAINT `fk_pack_event_pack` FOREIGN KEY (`id_pack`) REFERENCES `pack` (`id-pack`) ON DELETE CASCADE,
  CONSTRAINT `fk_pack_event_event` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `post` varchar(50) DEFAULT NULL,
  `nber_invi` int(11) NOT NULL DEFAULT 0,
  `id_user` int(11) DEFAULT NULL,
  `id_event` int(11) DEFAULT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_inscription`),
  KEY `idx_inscription_user` (`id_user`),
  KEY `idx_inscription_event` (`id_event`),
  CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `historique` (
  `id_historique` INT(11)     NOT NULL AUTO_INCREMENT,
  `action`        VARCHAR(50) NOT NULL,
  `entite`        VARCHAR(50) NOT NULL,
  `description`   TEXT        NOT NULL,
  `date_action`   DATETIME    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mail` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(120) NOT NULL,
  `id event`   INT(11)      DEFAULT NULL,
  `sujet`      VARCHAR(255) NOT NULL,
  `text`       TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mail_event` (`id event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `forums` (
  `id-publication` int(11) NOT NULL,
  `titre` varchar(30) NOT NULL,
  `contenu` text NOT NULL,
  `date-publication` date NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id-publication`),
  KEY `idx_forums_user` (`id_user`),
  CONSTRAINT `forums_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commentaire` (
  `id-commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date-commentaire` date NOT NULL,
  `id-publication` int(11) NOT NULL,
  PRIMARY KEY (`id-commentaire`),
  KEY `idx_comment_publication` (`id-publication`),
  CONSTRAINT `commonter_fks` FOREIGN KEY (`id-publication`) REFERENCES `forums` (`id-publication`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `projet` (
  `id-projet` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `discription` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL,
  `id-user` int(11) NOT NULL,
  `id-offre` int(11) NOT NULL,
  PRIMARY KEY (`id-projet`),
  KEY `idx_projet_user` (`id-user`),
  KEY `idx_projet_offer` (`id-offre`),
  CONSTRAINT `projet_ibfk_1` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `projet_ibfk_2` FOREIGN KEY (`id-offre`) REFERENCES `offer` (`id-offer`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sponsor` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `discription` text NOT NULL,
  PRIMARY KEY (`id_user`),
  CONSTRAINT `sponsor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `admin` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sponsorships` (
  `id-sponser` int(11) NOT NULL,
  `id-projet` int(11) NOT NULL,
  `sponser-nom` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `status` enum('actif','termine','annule','') NOT NULL,
  PRIMARY KEY (`id-sponser`),
  KEY `idx_sponsorship_project` (`id-projet`),
  CONSTRAINT `sponsorships_ibfk_1` FOREIGN KEY (`id-projet`) REFERENCES `projet` (`id-projet`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `condidateur` (
  `id_user` int(11) NOT NULL,
  `id-offer` int(11) NOT NULL,
  `cv` varchar(200) NOT NULL,
  `lettre-de-motivation` text NOT NULL,
  `date-envoia` date NOT NULL,
  `staut` varchar(50) NOT NULL,
  PRIMARY KEY (`id_user`,`id-offer`),
  KEY `idx_condidateur_offer` (`id-offer`),
  CONSTRAINT `condidateur_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `condidateur_ibfk_2` FOREIGN KEY (`id-offer`) REFERENCES `offer` (`id-offer`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- MINIMAL SEEDS (admin + demo data)
-- =========================================
INSERT INTO `user` (`id_user`, `email`, `mdp`, `role`, `tel`, `is_verified`, `is_online`)
VALUES
  (1, 'admin@gmail.com', '$2y$10$XwbcOnvFMwKb9C5x0QHg6esQ8H0Fz4P3oQK/yv8VQ7AoXH4jTq2T6', 'admin', '00000000', 1, 0),
  (2, 'jane.smith@email.com', '$2y$10$XwbcOnvFMwKb9C5x0QHg6esQ8H0Fz4P3oQK/yv8VQ7AoXH4jTq2T6', 'condidat', '23456789', 1, 0)
ON DUPLICATE KEY UPDATE
  `role` = VALUES(`role`),
  `is_verified` = VALUES(`is_verified`);

INSERT INTO `admin` (`id_user`, `nom`, `penom`, `code-admin`, `ddn`)
VALUES (1, 'Admin', 'DigiWork', 'ADM00001', '1990-01-01')
ON DUPLICATE KEY UPDATE `nom` = VALUES(`nom`);

INSERT INTO `pack` (`id-pack`, `nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`)
VALUES
  (1, 'PACK BASIC', 0.0, '2026-12-31', 'Pack debutant', 3, 'non'),
  (2, 'PACK PRO', 35.0, '2026-12-31', 'Pack standard', 15, 'oui')
ON DUPLICATE KEY UPDATE `prix` = VALUES(`prix`);

INSERT INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`)
VALUES (1, 2, '2026-01-01', '2026-12-31', 'actif')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `abon-pack` (`id-pack`, `id-abonnement`)
VALUES (2, 1)
ON DUPLICATE KEY UPDATE `id-pack` = VALUES(`id-pack`);

-- ── Entreprise seeds (required for offer FK) ──────────────────────────────────
INSERT INTO `entreprise` (`id-entr`, `id_user`, `nom-ent`, `adress`, `discreption`)
VALUES
  (1, 1, 'DigiWork Hub',    'Tunis, Tunisie',   'Plateforme intelligente pour entrepreneurs digitaux'),
  (2, 2, 'TechStart SARL',  'Sousse, Tunisie',  'Startup spécialisée en développement web et mobile'),
  (3, 1, 'InnovateTN',      'Sfax, Tunisie',    'Agence digitale et conseil en transformation numérique')
ON DUPLICATE KEY UPDATE `nom-ent` = VALUES(`nom-ent`);

-- ── Offer seeds ───────────────────────────────────────────────────────────────
INSERT INTO `offer` (`id-offer`, `titre`, `discription`, `competence`, `date-limiter`, `adresse`, `type`, `id-enter`)
VALUES
  (1, 'Dev Web React',    'Développement d\'une application web React pour startup fintech',    'React, JS, CSS',       '2026-12-31', 'Tunis',  'CDI',       1),
  (2, 'Dev Mobile',       'Création d\'une application mobile cross-platform Flutter',          'Flutter, Dart',        '2026-11-30', 'Sousse', 'Freelance', 2),
  (3, 'Designer UI/UX',   'Conception d\'interfaces utilisateur pour plateforme SaaS',          'Figma, Adobe XD',      '2026-10-31', 'Sfax',   'CDD',       3),
  (4, 'Dev Backend PHP',  'Développement API REST PHP pour système de gestion',                 'PHP, MySQL, REST',     '2026-12-15', 'Tunis',  'CDI',       1),
  (5, 'Data Analyst',     'Analyse de données et création de tableaux de bord BI',              'Python, SQL, Power BI','2026-11-15', 'Tunis',  'Freelance', 2)
ON DUPLICATE KEY UPDATE `titre` = VALUES(`titre`);

COMMIT;
