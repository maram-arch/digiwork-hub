-- ============================================================
-- DigiWork Hub — Migration v2
-- Ajouter après avoir importé le SQL original
-- ============================================================

USE digiwork_hub;

-- 1. Nouvelles colonnes sur la table forums
ALTER TABLE `forums`
    ADD COLUMN `categorie`   ENUM('general','stage','job','question','evenement') NOT NULL DEFAULT 'general' AFTER `contenu`,
    ADD COLUMN `image`       VARCHAR(255)  NULL AFTER `categorie`,
    ADD COLUMN `statut`      ENUM('active','archivee') NOT NULL DEFAULT 'active' AFTER `image`,
    ADD COLUMN `nb_vues`     INT           NOT NULL DEFAULT 0 AFTER `statut`,
    ADD COLUMN `nb_likes`    INT           NOT NULL DEFAULT 0 AFTER `nb_vues`,
    ADD COLUMN `is_event`    TINYINT(1)    NOT NULL DEFAULT 0 AFTER `nb_likes`,
    ADD COLUMN `event_date`  DATE          NULL AFTER `is_event`,
    ADD COLUMN `event_lieu`  VARCHAR(255)  NULL AFTER `event_date`;

-- 2. Table likes (empêche le double like)
CREATE TABLE IF NOT EXISTS `publication_likes` (
    `id`             INT NOT NULL AUTO_INCREMENT,
    `id_publication` INT NOT NULL,
    `id_user`        INT NOT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_like` (`id_publication`, `id_user`),
    CONSTRAINT `likes_fk_pub`  FOREIGN KEY (`id_publication`) REFERENCES `forums`(`id_publication`) ON DELETE CASCADE,
    CONSTRAINT `likes_fk_user` FOREIGN KEY (`id_user`)        REFERENCES `user`(`id_user`)           ON DELETE CASCADE
);

-- 3. Ajouter nom + prénom à la table user (pour afficher l'auteur)
ALTER TABLE `user`
    ADD COLUMN `nom`    VARCHAR(50) NULL AFTER `email`,
    ADD COLUMN `prenom` VARCHAR(50) NULL AFTER `nom`;

-- 4. Dossier uploads (à créer manuellement dans ton projet)
-- mkdir -p public/uploads/publications