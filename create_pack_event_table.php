<?php
// Script pour créer la table de relation pack-événement
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Utiliser la base de données
    $pdo->exec('USE `digiwork-hub`');
    
    // Créer la table pack_event
    $sql = 'CREATE TABLE IF NOT EXISTS `pack_event` (
      `id_pack_event` int(11) NOT NULL AUTO_INCREMENT,
      `id_pack` int(11) NOT NULL,
      `id_event` int(11) NOT NULL,
      `statut` enum("actif","inactif","en_attente") NOT NULL DEFAULT "actif",
      `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id_pack_event`),
      KEY `fk_pack_event_pack` (`id_pack`),
      KEY `fk_pack_event_event` (`id_event`),
      CONSTRAINT `fk_pack_event_pack` FOREIGN KEY (`id_pack`) REFERENCES `pack` (`id_pack`) ON DELETE CASCADE,
      CONSTRAINT `fk_pack_event_event` FOREIGN KEY (`id_event`) REFERENCES `evente` (`id_event`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    
    $pdo->exec($sql);
    echo "✅ Table pack_event créée avec succès<br>";
    
    // Insérer des données de test
    $testData = [
        [1, 1, 'actif'],
        [2, 1, 'inactif'],
        [1, 2, 'actif'],
        [3, 3, 'en_attente']
    ];
    
    foreach ($testData as $data) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO pack_event (id_pack, id_event, statut) VALUES (?, ?, ?)');
        $stmt->execute($data);
    }
    echo "✅ Données de test insérées<br>";
    
    // Vérifier la création
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM pack_event');
    $result = $stmt->fetch();
    echo "📊 Nombre de relations pack-événement: " . $result['total'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?>
