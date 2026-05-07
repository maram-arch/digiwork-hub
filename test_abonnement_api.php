<?php
// Script pour tester l'API des abonnements
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h1>🧪 Test API Abonnements</h1>";
    
    // Test 1: Créer des abonnements manuellement
    echo "<h2>1. Création manuelle d'abonnements</h2>";
    
    // Vérifier s'il y a déjà des abonnements
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM abonnement");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "<p>📝 Création d'abonnements de test...</p>";
        
        // Créer 4 abonnements de test
        $testData = [
            [2, '2024-01-01', '2024-12-31', 'actif', 4], // user 2, pack PREMIUM
            [3, '2024-02-01', '2024-11-30', 'actif', 5], // user 3, pack PRO
            [4, '2023-06-01', '2024-05-31', 'expiré', 6], // user 4, pack BASIC
            [5, '2024-03-01', '2025-02-28', 'en_attente', 5] // user 5, pack PRO
        ];
        
        foreach ($testData as $data) {
            $pdo->beginTransaction();
            
            // Insérer abonnement
            $stmt = $pdo->prepare("INSERT INTO abonnement (id_user, date_deb, date_fin, statut) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data[0], $data[1], $data[2], $data[3]]);
            $id_abo = $pdo->lastInsertId();
            
            // Lier au pack
            $stmt = $pdo->prepare("INSERT INTO `abon-pack` (id_abonnement, id_pack) VALUES (?, ?)");
            $stmt->execute([$id_abo, $data[4]]);
            
            $pdo->commit();
            echo "<p>✅ Abonnement $id_abo créé</p>";
        }
    } else {
        echo "<p>ℹ️ $count abonnements existent déjà</p>";
    }
    
    // Test 2: Vérifier la requête du contrôleur
    echo "<h2>2. Test de la requête getAll</h2>";
    
    $sql = "SELECT 
        a.id_abonnement,
        a.id_user,
        a.date_deb,
        a.date_fin,
        a.statut,
        u.email,
        u.tel,
        p.nom_pack,
        p.prix
    FROM abonnement a
    LEFT JOIN `user` u ON a.id_user = u.id_user
    LEFT JOIN `abon-pack` ap ON a.id_abonnement = ap.`id-abonnement`
    LEFT JOIN pack p ON ap.`id-pack` = p.id_pack
    ORDER BY a.date_deb DESC";
    
    $stmt = $pdo->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Résultat de la requête</h3>";
    echo "<p>📈 Nombre d'abonnements trouvés: " . count($result) . "</p>";
    
    if (!empty($result)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Email</th><th>Téléphone</th><th>Pack</th><th>Date Début</th><th>Date Fin</th><th>Statut</th>";
        echo "</tr>";
        
        foreach ($result as $row) {
            $statusClass = $row['statut'] === 'actif' ? 'color: green;' : ($row['statut'] === 'expiré' ? 'color: red;' : 'color: orange;');
            echo "<tr>";
            echo "<td>{$row['id_abonnement']}</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tel']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nom_pack']) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($row['date_deb'])) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($row['date_fin'])) . "</td>";
            echo "<td style='font-weight: bold; $statusClass'>" . ucfirst($row['statut']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test 3: Test de l'API JSON
    echo "<h2>3. Test de l'API JSON</h2>";
    
    // Simuler l'appel à l'API
    $_GET['action'] = 'getAll';
    
    ob_start();
    include 'controller/AbonnementController.php';
    $jsonOutput = ob_get_clean();
    
    echo "<h3>📤 Sortie JSON de l'API:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($jsonOutput);
    echo "</pre>";
    
    // Décoder et vérifier
    $data = json_decode($jsonOutput, true);
    if (is_array($data)) {
        echo "<p>✅ API retourne " . count($data) . " abonnements</p>";
    } else {
        echo "<p>❌ Erreur dans l'API</p>";
    }
    
    echo "<h2>🔗 Liens d'accès</h2>";
    echo "<p><a href='view/back/dashboard_abonnements.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Tableau des abonnements</a></p>";
    echo "<p><a href='controller/AbonnementController.php?action=getAll' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔌 API JSON directe</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
