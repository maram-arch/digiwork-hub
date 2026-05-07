<?php
// Script simple pour créer des abonnements de test
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h1>🔧 Création d'abonnements de test</h1>";
    
    // Créer des abonnements de test avec les bonnes colonnes
    $testAbonnements = [
        [
            'id_user' => 2,
            'date_deb' => date('Y-m-d', strtotime('-30 days')),
            'date_fin' => date('Y-m-d', strtotime('+30 days')),
            'statut' => 'actif'
        ],
        [
            'id_user' => 3,
            'date_deb' => date('Y-m-d', strtotime('-15 days')),
            'date_fin' => date('Y-m-d', strtotime('+15 days')),
            'statut' => 'actif'
        ],
        [
            'id_user' => 4,
            'date_deb' => date('Y-m-d', strtotime('-60 days')),
            'date_fin' => date('Y-m-d', strtotime('-30 days')),
            'statut' => 'expiré'
        ],
        [
            'id_user' => 5,
            'date_deb' => date('Y-m-d', strtotime('-5 days')),
            'date_fin' => date('Y-m-d', strtotime('+25 days')),
            'statut' => 'actif'
        ]
    ];
    
    // Récupérer les packs disponibles
    $stmt = $pdo->query("SELECT id_pack FROM pack ORDER BY id_pack");
    $packs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>📦 Création d'abonnements de test...</p>";
    
    foreach ($testAbonnements as $index => $abo) {
        // Insérer l'abonnement
        $sql = "INSERT INTO abonnement (id_user, date_deb, date_fin, statut) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$abo['id_user'], $abo['date_deb'], $abo['date_fin'], $abo['statut']]);
        $id_abonnement = $pdo->lastInsertId();
        
        // Lier l'abonnement à un pack
        $pack_id = $packs[$index % count($packs)];
        $sql = "INSERT INTO `abon-pack` (id_abonnement, id_pack) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_abonnement, $pack_id]);
        
        echo "<p>✅ Abonnement $id_abonnement créé pour l'utilisateur {$abo['id_user']} avec le pack $pack_id</p>";
    }
    
    // Vérifier le résultat
    echo "<h2>📊 Résultat</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM abonnement");
    $total = $stmt->fetch()['total'];
    echo "<p>📈 Total d'abonnements: <strong>$total</strong></p>";
    
    // Afficher les détails
    echo "<h3>📋 Détails des abonnements créés</h3>";
    $stmt = $pdo->query("SELECT 
        a.id_abonnement,
        u.email as client,
        u.tel,
        p.nom_pack,
        a.date_deb,
        a.date_fin,
        a.statut
    FROM abonnement a
    LEFT JOIN `user` u ON a.id_user = u.id_user
    LEFT JOIN `abon-pack` ap ON a.id_abonnement = ap.id_abonnement
    LEFT JOIN pack p ON ap.id_pack = p.id_pack
    ORDER BY a.date_deb DESC
    LIMIT 10");
    
    $abonnements = $stmt->fetchAll();
    
    if (!empty($abonnements)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Client</th><th>Téléphone</th><th>Pack</th><th>Date Début</th><th>Date Fin</th><th>Statut</th>";
        echo "</tr>";
        
        foreach ($abonnements as $abo) {
            $statusClass = $abo['statut'] === 'actif' ? 'color: green;' : ($abo['statut'] === 'expiré' ? 'color: red;' : 'color: orange;');
            echo "<tr>";
            echo "<td>{$abo['id_abonnement']}</td>";
            echo "<td>{$abo['client']}</td>";
            echo "<td>{$abo['tel']}</td>";
            echo "<td>{$abo['nom_pack']}</td>";
            echo "<td>" . date('d/m/Y', strtotime($abo['date_deb'])) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($abo['date_fin'])) . "</td>";
            echo "<td style='font-weight: bold; $statusClass'>" . ucfirst($abo['statut']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🔗 Liens d'accès</h2>";
    echo "<p><a href='view/back/dashboard_abonnements.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Voir les abonnements</a></p>";
    echo "<p><a href='view/back/dashboard.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Tableau de bord</a></p>";
    
    echo "<h2>✅ Données de test créées avec succès!</h2>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
