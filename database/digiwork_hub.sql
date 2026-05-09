-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 09 mai 2026 à 14:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `digiwork_hub`
--

-- --------------------------------------------------------

--
-- Structure de la table `abon-pack`
--

CREATE TABLE `abon-pack` (
  `id-pack` int(11) NOT NULL,
  `id-abonnement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `abon-pack`
--

INSERT INTO `abon-pack` (`id-pack`, `id-abonnement`) VALUES
(2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

CREATE TABLE `abonnement` (
  `id-abonnement` int(11) NOT NULL,
  `id-user` int(11) NOT NULL,
  `date-deb` date NOT NULL,
  `date-fin` date NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `abonnement`
--

INSERT INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`) VALUES
(1, 2, '2026-01-01', '2026-12-31', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `penom` varchar(20) NOT NULL,
  `code-admin` varchar(8) NOT NULL,
  `ddn` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id_user`, `nom`, `penom`, `code-admin`, `ddn`) VALUES
(1, 'Admin', 'DigiWork', 'ADM00001', '1990-01-01');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` datetime NOT NULL DEFAULT current_timestamp(),
  `id_publication` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `condidat`
--

CREATE TABLE `condidat` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `ddn` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `condidateur`
--

CREATE TABLE `condidateur` (
  `id_user` int(11) NOT NULL,
  `id-offer` int(11) NOT NULL,
  `cv` varchar(200) NOT NULL,
  `lettre-de-motivation` text NOT NULL,
  `date-envoia` date NOT NULL,
  `staut` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
  `id-entr` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nom-ent` varchar(30) NOT NULL,
  `adress` varchar(50) NOT NULL,
  `discreption` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `entreprise`
--

INSERT INTO `entreprise` (`id-entr`, `id_user`, `nom-ent`, `adress`, `discreption`) VALUES
(1, 1, 'InnovateTN', 'Tunis, Tunisie', 'Plateforme intelligente pour entrepreneurs digitaux'),
(2, 2, 'TechStart SARL', 'Sousse, Tunisie', 'Startup spécialisée en développement web et mobile');

-- --------------------------------------------------------

--
-- Structure de la table `evente`
--

CREATE TABLE `evente` (
  `id_event` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_event` date DEFAULT NULL,
  `heure_event` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `id_organisateur` int(11) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `nbr_inscri` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id_publication` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forums`
--

CREATE TABLE `forums` (
  `id_publication` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` datetime NOT NULL DEFAULT current_timestamp(),
  `id_user` int(11) NOT NULL,
  `categorie` varchar(50) DEFAULT 'general',
  `nb_likes` int(11) DEFAULT 0,
  `nb_vues` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_event` tinyint(1) DEFAULT 0,
  `event_date` date DEFAULT NULL,
  `event_lieu` varchar(255) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'active',
  `embedding` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique`
--

CREATE TABLE `historique` (
  `id_historique` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entite` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `post` varchar(50) DEFAULT NULL,
  `nber_invi` int(11) NOT NULL DEFAULT 0,
  `id_user` int(11) DEFAULT NULL,
  `id_event` int(11) DEFAULT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mail`
--

CREATE TABLE `mail` (
  `id` int(11) NOT NULL,
  `email` varchar(120) NOT NULL,
  `id event` int(11) DEFAULT NULL,
  `sujet` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offer`
--

CREATE TABLE `offer` (
  `id-offer` int(11) NOT NULL,
  `titre` varchar(20) NOT NULL,
  `discription` text NOT NULL,
  `competence` varchar(30) NOT NULL,
  `date-limiter` date NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `id-enter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `context` varchar(20) NOT NULL DEFAULT 'signup',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pack`
--

CREATE TABLE `pack` (
  `id-pack` int(11) NOT NULL,
  `nom-pack` varchar(20) NOT NULL,
  `prix` float NOT NULL,
  `duree` date NOT NULL,
  `description` text NOT NULL,
  `nb-proj-max` int(11) NOT NULL,
  `support-prioritaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pack`
--

INSERT INTO `pack` (`id-pack`, `nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`) VALUES
(1, 'PACK BASIC', 0, '2026-12-31', 'Pack debutant', 3, 'non'),
(2, 'PACK PRO', 35, '2026-12-31', 'Pack standard', 15, 'oui');

-- --------------------------------------------------------

--
-- Structure de la table `pack_event`
--

CREATE TABLE `pack_event` (
  `id_pack_event` int(11) NOT NULL,
  `id_pack` int(11) NOT NULL,
  `id_event` int(11) NOT NULL,
  `statut` enum('actif','inactif','en_attente') NOT NULL DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `projet`
--

CREATE TABLE `projet` (
  `id-projet` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `discription` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL,
  `id-user` int(11) NOT NULL,
  `id-offre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `publication_likes`
--

CREATE TABLE `publication_likes` (
  `id_publication` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sponsor`
--

CREATE TABLE `sponsor` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `discription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sponsorships`
--

CREATE TABLE `sponsorships` (
  `id-sponser` int(11) NOT NULL,
  `id-projet` int(11) NOT NULL,
  `sponser-nom` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `status` enum('actif','termine','annule','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `email` varchar(120) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` enum('condidat','admin','entreprise','sponsor') NOT NULL DEFAULT 'condidat',
  `tel` varchar(20) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `last_activity` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `email`, `mdp`, `role`, `tel`, `is_verified`, `is_online`, `last_activity`, `created_at`, `nom`, `prenom`) VALUES
(1, 'admin@gmail.com', '$2y$10$XwbcOnvFMwKb9C5x0QHg6esQ8H0Fz4P3oQK/yv8VQ7AoXH4jTq2T6', 'admin', '00000000', 1, 0, NULL, '2026-05-09 11:56:32', NULL, NULL),
(2, 'jane.smith@email.com', '$2y$10$XwbcOnvFMwKb9C5x0QHg6esQ8H0Fz4P3oQK/yv8VQ7AoXH4jTq2T6', 'condidat', '23456789', 1, 0, NULL, '2026-05-09 11:56:32', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users_interactions`
--

CREATE TABLE `users_interactions` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_publication` int(11) NOT NULL,
  `type_interaction` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_interactions`
--

CREATE TABLE `user_interactions` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_publication` int(11) NOT NULL,
  `type_interaction` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abon-pack`
--
ALTER TABLE `abon-pack`
  ADD PRIMARY KEY (`id-pack`,`id-abonnement`),
  ADD KEY `idx_abon_pack_abonnement` (`id-abonnement`);

--
-- Index pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id-abonnement`),
  ADD KEY `idx_abonnement_user` (`id-user`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `idx_comment_publication` (`id_publication`);

--
-- Index pour la table `condidat`
--
ALTER TABLE `condidat`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `condidateur`
--
ALTER TABLE `condidateur`
  ADD PRIMARY KEY (`id_user`,`id-offer`),
  ADD KEY `idx_condidateur_offer` (`id-offer`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD PRIMARY KEY (`id-entr`),
  ADD UNIQUE KEY `uq_entreprise_user` (`id_user`);

--
-- Index pour la table `evente`
--
ALTER TABLE `evente`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `idx_event_user` (`id_organisateur`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id_publication`,`id_user`);

--
-- Index pour la table `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`id_publication`),
  ADD KEY `idx_forums_user` (`id_user`);

--
-- Index pour la table `historique`
--
ALTER TABLE `historique`
  ADD PRIMARY KEY (`id_historique`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `idx_inscription_user` (`id_user`),
  ADD KEY `idx_inscription_event` (`id_event`);

--
-- Index pour la table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mail_event` (`id event`);

--
-- Index pour la table `offer`
--
ALTER TABLE `offer`
  ADD PRIMARY KEY (`id-offer`),
  ADD KEY `idx_offer_enter` (`id-enter`);

--
-- Index pour la table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_user` (`id_user`),
  ADD KEY `idx_otp_user_context` (`id_user`,`context`);

--
-- Index pour la table `pack`
--
ALTER TABLE `pack`
  ADD PRIMARY KEY (`id-pack`);

--
-- Index pour la table `pack_event`
--
ALTER TABLE `pack_event`
  ADD PRIMARY KEY (`id_pack_event`),
  ADD UNIQUE KEY `uq_pack_event` (`id_pack`,`id_event`),
  ADD KEY `idx_pack_event_event` (`id_event`);

--
-- Index pour la table `projet`
--
ALTER TABLE `projet`
  ADD PRIMARY KEY (`id-projet`),
  ADD KEY `idx_projet_user` (`id-user`),
  ADD KEY `idx_projet_offer` (`id-offre`);

--
-- Index pour la table `publication_likes`
--
ALTER TABLE `publication_likes`
  ADD PRIMARY KEY (`id_publication`,`id_user`);

--
-- Index pour la table `sponsor`
--
ALTER TABLE `sponsor`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `sponsorships`
--
ALTER TABLE `sponsorships`
  ADD PRIMARY KEY (`id-sponser`),
  ADD KEY `idx_sponsorship_project` (`id-projet`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_user_email` (`email`);

--
-- Index pour la table `users_interactions`
--
ALTER TABLE `users_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ui_user` (`id_user`),
  ADD KEY `idx_ui_publication` (`id_publication`);

--
-- Index pour la table `user_interactions`
--
ALTER TABLE `user_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ui_user` (`id_user`),
  ADD KEY `idx_ui_publication` (`id_publication`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abonnement`
--
ALTER TABLE `abonnement`
  MODIFY `id-abonnement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evente`
--
ALTER TABLE `evente`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forums`
--
ALTER TABLE `forums`
  MODIFY `id_publication` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `historique`
--
ALTER TABLE `historique`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pack`
--
ALTER TABLE `pack`
  MODIFY `id-pack` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `pack_event`
--
ALTER TABLE `pack_event`
  MODIFY `id_pack_event` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users_interactions`
--
ALTER TABLE `users_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_interactions`
--
ALTER TABLE `user_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abon-pack`
--
ALTER TABLE `abon-pack`
  ADD CONSTRAINT `abon-pack_ibfk_1` FOREIGN KEY (`id-pack`) REFERENCES `pack` (`id-pack`) ON DELETE CASCADE,
  ADD CONSTRAINT `abon-pack_ibfk_2` FOREIGN KEY (`id-abonnement`) REFERENCES `abonnement` (`id-abonnement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD CONSTRAINT `abonnemet_pk` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_fk` FOREIGN KEY (`id_publication`) REFERENCES `forums` (`id_publication`) ON DELETE CASCADE;

--
-- Contraintes pour la table `condidat`
--
ALTER TABLE `condidat`
  ADD CONSTRAINT `condidat_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `condidateur`
--
ALTER TABLE `condidateur`
  ADD CONSTRAINT `condidateur_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `condidateur_ibfk_2` FOREIGN KEY (`id-offer`) REFERENCES `offer` (`id-offer`) ON DELETE CASCADE;

--
-- Contraintes pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD CONSTRAINT `entreprise_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evente`
--
ALTER TABLE `evente`
  ADD CONSTRAINT `fk_event_user` FOREIGN KEY (`id_organisateur`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `forums`
--
ALTER TABLE `forums`
  ADD CONSTRAINT `forums_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offer`
--
ALTER TABLE `offer`
  ADD CONSTRAINT `offer_ibfk_1` FOREIGN KEY (`id-enter`) REFERENCES `entreprise` (`id-entr`) ON DELETE CASCADE;

--
-- Contraintes pour la table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pack_event`
--
ALTER TABLE `pack_event`
  ADD CONSTRAINT `fk_pack_event_event` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pack_event_pack` FOREIGN KEY (`id_pack`) REFERENCES `pack` (`id-pack`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projet`
--
ALTER TABLE `projet`
  ADD CONSTRAINT `projet_ibfk_1` FOREIGN KEY (`id-user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `projet_ibfk_2` FOREIGN KEY (`id-offre`) REFERENCES `offer` (`id-offer`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sponsor`
--
ALTER TABLE `sponsor`
  ADD CONSTRAINT `sponsor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `admin` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sponsorships`
--
ALTER TABLE `sponsorships`
  ADD CONSTRAINT `sponsorships_ibfk_1` FOREIGN KEY (`id-projet`) REFERENCES `projet` (`id-projet`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
