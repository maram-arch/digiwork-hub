<?php
// Script pour créer le pack KIMA
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    $pdo->exec('USE `digiwork-hub`');
    
    echo "<h1>🎁 Création du Pack KIMA</h1>";
    
    // Créer le pack KIMA avec des caractéristiques premium
    $packData = [
        'nom_pack' => 'PACK KIMA',
        'prix' => 99.99,
        'duree' => 365,
        'description' => 'Pack premium KIMA - Accès complet à toutes les fonctionnalités de DigiWork Hub. Idéal pour les entrepreneurs digitaux qui souhaitent une solution tout-en-un avec support prioritaire et projets illimités.',
        'nb_proj_max' => 999,
        'support_prioritaire' => 'oui'
    ];
    
    // Insérer le pack KIMA
    $sql = "INSERT INTO pack (nom_pack, prix, duree, description, nb_proj_max, support_prioritaire) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([
        $packData['nom_pack'],
        $packData['prix'],
        $packData['duree'],
        $packData['description'],
        $packData['nb_proj_max'],
        $packData['support_prioritaire']
    ])) {
        $id_pack = $pdo->lastInsertId();
        echo "<p>✅ Pack KIMA créé avec succès (ID: $id_pack)</p>";
        
        // Afficher les détails
        echo "<h2>📋 Détails du pack créé:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Caractéristique</th><th>Valeur</th>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Nom du pack</strong></td>";
        echo "<td>" . htmlspecialchars($packData['nom_pack']) . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Prix</strong></td>";
        echo "<td>" . number_format($packData['prix'], 2, ',', ' ') . " €</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Durée</strong></td>";
        echo "<td>" . $packData['duree'] . " jours</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Nombre de projets max</strong></td>";
        echo "<td>" . number_format($packData['nb_proj_max'], 0, ',', ' ') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Support prioritaire</strong></td>";
        echo "<td>" . ($packData['support_prioritaire'] === 'oui' ? '✅ Oui' : '❌ Non') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Description</strong></td>";
        echo "<td>" . htmlspecialchars($packData['description']) . "</td>";
        echo "</tr>";
        
        echo "</table>";
        
        // Créer un abonnement de test pour ce pack
        echo "<h2>👤 Création d'un abonnement de test</h2>";
        
        // Récupérer un utilisateur existant
        $stmt = $pdo->query("SELECT id_user FROM `user` LIMIT 1");
        $user = $stmt->fetch();
        
        if ($user) {
            $abonnementData = [
                'id_user' => $user['id_user'],
                'date_deb' => date('Y-m-d'),
                'date_fin' => date('Y-m-d', strtotime('+1 year')),
                'statut' => 'actif'
            ];
            
            // Créer l'abonnement
            $sql = "INSERT INTO abonnement (id_user, date_deb, date_fin, statut) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $abonnementData['id_user'],
                $abonnementData['date_deb'],
                $abonnementData['date_fin'],
                $abonnementData['statut']
            ]);
            $id_abonnement = $pdo->lastInsertId();
            
            // Lier l'abonnement au pack KIMA
            $sql = "INSERT INTO `abon-pack` (`id-abonnement`, `id-pack`) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_abonnement, $id_pack]);
            
            echo "<p>✅ Abonnement de test créé (ID: $id_abonnement) pour l'utilisateur ID: {$user['id_user']}</p>";
        }
        
        echo "<h2>🔗 Liens d'accès</h2>";
        echo "<p><a href='view/front/packs.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📦 Voir les packs</a></p>";
        echo "<p><a href='view/back/dashboard_packs.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>⚙️ Gérer les packs</a></p>";
        echo "<p><a href='view/back/dashboard_abonnements.php' style='display: inline-block; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Gérer les abonnements</a></p>";
        echo "<p><a href='view/back/dashboard.php' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Tableau de bord</a></p>";
        
        echo "<h2>🎉 Pack KIMA ajouté avec succès!</h2>";
        echo "<p>Le pack KIMA est maintenant disponible dans le système et peut être utilisé pour créer des abonnements.</p>";
        
    } else {
        echo "<p>❌ Erreur lors de la création du pack: " . $stmt->errorInfo()[2] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
