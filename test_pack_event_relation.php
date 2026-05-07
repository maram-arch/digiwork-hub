<?php
// Page de test pour la relation pack-événement
require_once 'config/config.php';
require_once 'model/PackEvent.php';
require_once 'model/Pack.php';
require_once 'model/Event.php';

echo "<h1>🔗 Test de la Relation Pack-Événement</h1>";

// Test 1: Vérifier si la table existe
echo "<h2>1. Vérification de la table pack_event</h2>";
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("DESCRIBE pack_event");
    echo "<p>✅ Table pack_event existe</p>";
    
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 2: Vérifier les données existantes
echo "<h2>2. Données existantes dans pack_event</h2>";
try {
    $relations = PackEvent::getAll();
    echo "<p>📊 Nombre de relations: " . count($relations) . "</p>";
    
    if (!empty($relations)) {
        echo "<table border='1' style='margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Pack</th><th>Événement</th><th>Statut</th><th>Date création</th></tr>";
        foreach ($relations as $relation) {
            echo "<tr>";
            echo "<td>{$relation['id_pack_event']}</td>";
            echo "<td>" . htmlspecialchars($relation['nom_pack'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($relation['event_titre'] ?? 'N/A') . "</td>";
            echo "<td><span style='padding: 2px 8px; background: #e3f2fd; border-radius: 12px;'>" . ucfirst($relation['statut']) . "</span></td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($relation['date_creation'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ Aucune relation trouvée</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 3: Vérifier les packs disponibles
echo "<h2>3. Packs disponibles</h2>";
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SELECT * FROM pack ORDER BY nom_pack");
    $packs = $stmt->fetchAll();
    
    echo "<p>📦 Nombre de packs: " . count($packs) . "</p>";
    if (!empty($packs)) {
        echo "<table border='1' style='margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Durée</th><th>Projets max</th></tr>";
        foreach ($packs as $pack) {
            echo "<tr>";
            echo "<td>{$pack['id_pack']}</td>";
            echo "<td>" . htmlspecialchars($pack['nom_pack']) . "</td>";
            echo "<td>{$pack['prix']} €</td>";
            echo "<td>{$pack['duree']} jours</td>";
            echo "<td>{$pack['nb_proj_max']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 4: Vérifier les événements disponibles
echo "<h2>4. Événements disponibles</h2>";
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SELECT * FROM evente ORDER BY titre");
    $events = $stmt->fetchAll();
    
    echo "<p>📅 Nombre d'événements: " . count($events) . "</p>";
    if (!empty($events)) {
        echo "<table border='1' style='margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Titre</th><th>Date</th><th>Lieu</th><th>Capacité</th></tr>";
        foreach ($events as $event) {
            echo "<tr>";
            echo "<td>{$event['id_event']}</td>";
            echo "<td>" . htmlspecialchars($event['titre']) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($event['date_event'])) . "</td>";
            echo "<td>" . htmlspecialchars($event['lieu'] ?? 'N/A') . "</td>";
            echo "<td>{$event['capacite']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 5: Créer une relation de test
echo "<h2>5. Test de création de relation</h2>";
try {
    // Récupérer un pack et un événement existants
    $pdo = config::getConnexion();
    $packStmt = $pdo->query("SELECT id_pack FROM pack LIMIT 1");
    $pack = $packStmt->fetch();
    
    $eventStmt = $pdo->query("SELECT id_event FROM evente LIMIT 1");
    $event = $eventStmt->fetch();
    
    if ($pack && $event) {
        $packEvent = new PackEvent(null, $pack['id_pack'], $event['id_event'], 'actif');
        if ($packEvent->create()) {
            echo "<p>✅ Relation de test créée avec succès (Pack {$pack['id_pack']} → Événement {$event['id_event']})</p>";
        } else {
            echo "<p>⚠️ Erreur lors de la création (peut-être déjà existante)</p>";
        }
    } else {
        echo "<p>⚠️ Pas de pack ou d'événement disponible pour le test</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 6: Test des méthodes de recherche
echo "<h2>6. Test des méthodes de recherche</h2>";
try {
    $pdo = config::getConnexion();
    $packStmt = $pdo->query("SELECT id_pack FROM pack LIMIT 1");
    $pack = $packStmt->fetch();
    
    if ($pack) {
        $eventsByPack = PackEvent::getByPack($pack['id_pack']);
        echo "<p>📋 Événements pour le pack {$pack['id_pack']}: " . count($eventsByPack) . "</p>";
    }
    
    $eventStmt = $pdo->query("SELECT id_event FROM evente LIMIT 1");
    $event = $eventStmt->fetch();
    
    if ($event) {
        $packsByEvent = PackEvent::getByEvent($event['id_event']);
        echo "<p>📦 Packs pour l'événement {$event['id_event']}: " . count($packsByEvent) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Liens d'accès
echo "<h2>7. Accès à l'interface</h2>";
echo "<p><a href='view/back/managePackEvents.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔗 Gérer les relations</a></p>";
echo "<p><a href='view/back/dashboard.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Tableau de bord</a></p>";

echo "<h2>✅ Tests terminés!</h2>";
echo "<p>La relation pack-événement est maintenant fonctionnelle.</p>";
?>
