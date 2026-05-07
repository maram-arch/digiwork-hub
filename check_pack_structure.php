<?php
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h2>📦 Structure de la table pack</h2>";
    $stmt = $pdo->query("DESCRIBE pack");
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>📦 Packs existants</h2>";
    $stmt = $pdo->query("SELECT * FROM pack");
    $packs = $stmt->fetchAll();
    
    if (!empty($packs)) {
        echo "<table border='1' style='margin: 10px 0;'>";
        echo "<tr>";
        foreach (array_keys($packs[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        foreach ($packs as $pack) {
            echo "<tr>";
            foreach ($pack as $value) {
                echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Aucun pack trouvé</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
