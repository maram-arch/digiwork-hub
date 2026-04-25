-- Seed data for abonnement functionality testing
USE `digiwork-hub`;

-- Insert test users if they don't exist
INSERT IGNORE INTO `user` (`id_user`, `email`, `mdp`, `tel`) VALUES
(1, 'john.doe@email.com', 'password123', 12345678),
(2, 'jane.smith@email.com', 'password123', 23456789),
(3, 'bob.wilson@email.com', 'password123', 34567890),
(4, 'alice.brown@email.com', 'password123', 45678901),
(5, 'charlie.davis@email.com', 'password123', 56789012);

-- Insert packs if they don't exist (these should match the ones created by packs.php)
INSERT IGNORE INTO `pack` (`id-pack`, `nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`) VALUES
(1, 'PACK BASIC (Débutant)', 0.0, '2025-02-20', '🎯 Cible

👉 Étudiants / débutants / nouveaux freelances

📦 Contenu
Accès limité à la plateforme
Nombre de projets max : 2–3
Pas de support prioritaire
Pas d\'outils avancés', 3, 'non'),
(2, 'PACK PRO (Standard)', 35.0, '2025-02-20', '🎯 Cible

👉 Freelances actifs / entrepreneurs en croissance

📦 Contenu
Accès complet aux fonctionnalités
Nombre de projets moyen (10–20)
Support normal
Accès aux recommandations', 15, 'oui'),
(3, 'PACK PREMIUM (Avancé)', 80.0, '2025-02-20', '🎯 Cible

👉 Freelances professionnels / agences / power users

📦 Contenu
Projets illimités
Support prioritaire
Mise en avant du profil
Accès aux analytics avancés
Accès aux meilleures opportunités', 9999, 'oui');

-- Insert test abonnements
INSERT IGNORE INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`) VALUES
(1, 1, '2025-01-15', '2025-02-14', 'actif'),
(2, 2, '2025-01-20', '2025-02-19', 'actif'),
(3, 3, '2024-12-01', '2025-01-01', 'expiré'),
(4, 4, '2025-01-10', '2025-02-09', 'actif'),
(5, 5, '2025-01-25', '2025-02-24', 'actif');

-- Link abonnements to packs
INSERT IGNORE INTO `abon-pack` (`id-pack`, `id-abonnement`) VALUES
(1, 1),  -- John Doe with Basic pack
(2, 2),  -- Jane Smith with Pro pack
(1, 3),  -- Bob Wilson with Basic pack (expired)
(3, 4),  -- Alice Brown with Premium pack
(2, 5);  -- Charlie Davis with Pro pack
