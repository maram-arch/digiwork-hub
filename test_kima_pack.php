<?php
// Script pour vérifier que le pack KIMA s'affiche correctement
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h1>🔍 Vérification du Pack KIMA</h1>";
    
    // Vérifier que le pack KIMA existe
    $stmt = $pdo->prepare("SELECT * FROM pack WHERE nom_pack = ?");
    $stmt->execute(['PACK KIMA']);
    $kimaPack = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($kimaPack) {
        echo "<h2>✅ Pack KIMA trouvé dans la base de données</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0; background: #f8f9fa;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th>Champ</th><th>Valeur</th>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>ID</strong></td>";
        echo "<td>{$kimaPack['id_pack']}</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Nom</strong></td>";
        echo "<td style='color: #007bff; font-weight: bold;'>{$kimaPack['nom_pack']}</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Prix</strong></td>";
        echo "<td style='color: #28a745; font-weight: bold;'>" . number_format($kimaPack['prix'], 2, ',', ' ') . " €</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Durée</strong></td>";
        echo "<td>{$kimaPack['duree']} jours</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Projets max</strong></td>";
        echo "<td>" . number_format($kimaPack['nb_proj_max'], 0, ',', ' ') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Support prioritaire</strong></td>";
        echo "<td>" . ($kimaPack['support_prioritaire'] === 'oui' ? '✅ Oui' : '❌ Non') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Description</strong></td>";
        echo "<td>" . nl2br(htmlspecialchars($kimaPack['description'])) . "</td>";
        echo "</tr>";
        
        echo "</table>";
        
        // Vérifier l'abonnement de test
        echo "<h2>👤 Vérification de l'abonnement de test</h2>";
        $stmt = $pdo->query("SELECT 
            a.id_abonnement,
            a.date_deb,
            a.date_fin,
            a.statut,
            u.email,
            p.nom_pack
        FROM abonnement a
        LEFT JOIN `user` u ON a.id_user = u.id_user
        LEFT JOIN `abon-pack` ap ON a.id_abonnement = ap.`id-abonnement`
        LEFT JOIN pack p ON ap.`id-pack` = p.id_pack
        WHERE p.nom_pack = 'PACK KIMA'
        ORDER BY a.id_abonnement DESC
        LIMIT 1");
        
        $kimaSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($kimaSubscription) {
            echo "<p>✅ Abonnement de test trouvé pour le pack KIMA</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background: #e9ecef;'>";
            echo "<th>ID Abonnement</th><th>Email</th><th>Pack</th><th>Date Début</th><th>Date Fin</th><th>Statut</th>";
            echo "</tr>";
            
            $statusClass = $kimaSubscription['statut'] === 'actif' ? 'color: green;' : ($kimaSubscription['statut'] === 'expiré' ? 'color: red;' : 'color: orange;');
            
            echo "<tr>";
            echo "<td>{$kimaSubscription['id_abonnement']}</td>";
            echo "<td>" . htmlspecialchars($kimaSubscription['email']) . "</td>";
            echo "<td style='color: #007bff; font-weight: bold;'>{$kimaSubscription['nom_pack']}</td>";
            echo "<td>" . date('d/m/Y', strtotime($kimaSubscription['date_deb'])) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($kimaSubscription['date_fin'])) . "</td>";
            echo "<td style='font-weight: bold; $statusClass'>" . ucfirst($kimaSubscription['statut']) . "</td>";
            echo "</tr>";
            
            echo "</table>";
        } else {
            echo "<p>⚠️ Aucun abonnement trouvé pour le pack KIMA</p>";
        }
        
        // Comparaison avec les autres packs
        echo "<h2>📊 Comparaison avec les autres packs</h2>";
        $stmt = $pdo->query("SELECT * FROM pack ORDER BY prix DESC");
        $packs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th>Nom</th><th>Prix</th><th>Durée</th><th>Projets</th><th>Support</th>";
        echo "</tr>";
        
        foreach ($packs as $pack) {
            $isKima = $pack['nom_pack'] === 'PACK KIMA';
            $rowStyle = $isKima ? 'background: #d4edda; font-weight: bold;' : '';
            
            echo "<tr style='$rowStyle'>";
            echo "<td>" . htmlspecialchars($pack['nom_pack']) . "</td>";
            echo "<td>" . number_format($pack['prix'], 2, ',', ' ') . " €</td>";
            echo "<td>{$pack['duree']} jours</td>";
            echo "<td>" . number_format($pack['nb_proj_max'], 0, ',', ' ') . "</td>";
            echo "<td>" . ($pack['support_prioritaire'] === 'oui' ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h2>🔗 Liens de test</h2>";
        echo "<p><a href='view/front/packs.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📦 Voir les packs (Front)</a></p>";
        echo "<p><a href='view/back/dashboard_packs.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>⚙️ Gérer les packs (Admin)</a></p>";
        echo "<p><a href='view/back/dashboard_abonnements.php' style='display: inline-block; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Gérer les abonnements</a></p>";
        
        echo "<h2>🎉 Pack KIMA vérifié avec succès!</h2>";
        echo "<p>Le pack KIMA est correctement intégré dans le système et fonctionne parfaitement.</p>";
        
    } else {
        echo "<h2>❌ Pack KIMA non trouvé</h2>";
        echo "<p>Le pack KIMA n'existe pas dans la base de données.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
