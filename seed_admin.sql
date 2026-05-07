-- Seed admin user: admin@gmail.com / 12345678
-- Run this against your `digiwork-hub` database

USE `digiwork-hub`;

-- 1. Insert into user table (plain text password as per schema)
INSERT INTO `user` (`id_user`, `email`, `mdp`, `tel`)
VALUES (1, 'admin@gmail.com', '12345678', 0)
ON DUPLICATE KEY UPDATE
    `email` = 'admin@gmail.com',
    `mdp`   = '12345678';

-- 2. Insert into admin table so the role check succeeds
INSERT INTO `admin` (`id_user`, `nom`, `penom`, `code-admin`, `ddn`)
VALUES (1, 'Admin', 'DigiWork', 'ADM00001', '1990-01-01')
ON DUPLICATE KEY UPDATE `nom` = 'Admin';
