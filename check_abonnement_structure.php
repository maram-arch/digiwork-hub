<?php
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h2>📋 Structure de la table abonnement</h2>";
    $stmt = $pdo->query("DESCRIBE abonnement");
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>📋 Structure de la table abon-pack</h2>";
    $stmt = $pdo->query("DESCRIBE `abon-pack`");
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
