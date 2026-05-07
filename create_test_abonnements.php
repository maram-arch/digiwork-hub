<?php
// Script pour créer des abonnements de test
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h1>🔧 Création d'abonnements de test</h1>";
    
    // Récupérer les utilisateurs existants
    $stmt = $pdo->query("SELECT id_user, email, tel FROM `user` ORDER BY id_user LIMIT 5");
    $users = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT id_pack, nom_pack, prix FROM pack ORDER BY id_pack");
    $packs = $stmt->fetchAll();
    
    if (!empty($users) && !empty($packs)) {
        echo "<p>📦 Création d'abonnements de test...</p>";
        
        // Créer des abonnements de test
        $testAbonnements = [];
        for ($i = 0; $i < min(5, count($users)); $i++) {
            $testAbonnements[] = [
                'user_id' => $users[$i]['id_user'],
                'pack_id' => $packs[$i % count($packs)]['id_pack'],
                'date_deb' => date('Y-m-d', strtotime('-' . ($i * 10) . ' days')),
                'date_fin' => date('Y-m-d', strtotime('+' . (30 - $i * 5) . ' days')),
                'status' => $i == 2 ? 'expiré' : ($i == 4 ? 'en_attente' : 'actif')
            ];
        }
        
        foreach ($testAbonnements as $abo) {
            // Insérer l'abonnement
            $stmt = $pdo->prepare("INSERT INTO abonnement (id_user, date_deb, date_fin, statut) VALUES (?, ?, ?, ?)");
            $stmt->execute([$abo['user_id'], $abo['date_deb'], $abo['date_fin'], $abo['status']]);
            $id_abonnement = $pdo->lastInsertId();
            
            // Lier l'abonnement au pack
            $stmt = $pdo->prepare("INSERT INTO `abon-pack` (id_abonnement, id_pack) VALUES (?, ?)");
            $stmt->execute([$id_abonnement, $abo['pack_id']]);
        }
        
        echo "<p>✅ " . count($testAbonnements) . " abonnements créés</p>";
    }
    
    // Vérifier le résultat
    echo "<h2>📊 Résultat</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM abonnement");
    $total = $stmt->fetch()['total'];
    echo "<p>📈 Total d'abonnements: <strong>$total</strong></p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as actifs FROM abonnement WHERE status = 'actif'");
    $actifs = $stmt->fetch()['actifs'];
    echo "<p>✅ Abonnements actifs: <strong>$actifs</strong></p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as expires FROM abonnement WHERE status = 'expiré'");
    $expires = $stmt->fetch()['expires'];
    echo "<p>⏰ Abonnements expirés: <strong>$expires</strong></p>";
    
    // Afficher les détails
    echo "<h3>📋 Détails des abonnements</h3>";
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
    ORDER BY a.date_deb DESC");
    
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
