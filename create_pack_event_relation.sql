-- Création de la table de relation entre les packs et les événements
CREATE TABLE IF NOT EXISTS `pack_event` (
  `id_pack_event` int(11) NOT NULL AUTO_INCREMENT,
  `id_pack` int(11) NOT NULL,
  `id_event` int(11) NOT NULL,
  `statut` enum('actif','inactif','en_attente') NOT NULL DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pack_event`),
  KEY `fk_pack_event_pack` (`id_pack`),
  KEY `fk_pack_event_event` (`id_event`),
  CONSTRAINT `fk_pack_event_pack` FOREIGN KEY (`id_pack`) REFERENCES `pack` (`id-pack`) ON DELETE CASCADE,
  CONSTRAINT `fk_pack_event_event` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertion de données de test pour la relation pack-événement
INSERT INTO `pack_event` (`id_pack`, `id_event`, `statut`) VALUES
(1, 1, 'actif'),
(2, 1, 'inactif'),
(1, 2, 'actif'),
(3, 3, 'en_attente');
