<?php
require_once(__DIR__ . "/config/config.php");

echo "<h2>Setting up test data for abonnement functionality...</h2>";

try {
    // Insert test users
    $users = [
        [1, 'john.doe@email.com', 'password123', 12345678],
        [2, 'jane.smith@email.com', 'password123', 23456789],
        [3, 'bob.wilson@email.com', 'password123', 34567890],
        [4, 'alice.brown@email.com', 'password123', 45678901],
        [5, 'charlie.davis@email.com', 'password123', 56789012]
    ];

    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `user` (`id_user`, `email`, `mdp`, `tel`) VALUES (?, ?, ?, ?)");
        $stmt->execute($user);
    }
    echo "<p>✓ Test users created</p>";

    // Insert packs
    $packs = [
        [1, 'PACK BASIC (Débutant)', 0.0, '2025-02-20', '🎯 Cible\n\n👉 Étudiants / débutants / nouveaux freelances\n\n📦 Contenu\nAccès limité à la plateforme\nNombre de projets max : 2–3\nPas de support prioritaire\nPas d\'outils avancés', 3, 'non'],
        [2, 'PACK PRO (Standard)', 35.0, '2025-02-20', '🎯 Cible\n\n👉 Freelances actifs / entrepreneurs en croissance\n\n📦 Contenu\nAccès complet aux fonctionnalités\nNombre de projets moyen (10–20)\nSupport normal\nAccès aux recommandations', 15, 'oui'],
        [3, 'PACK PREMIUM (Avancé)', 80.0, '2025-02-20', '🎯 Cible\n\n👉 Freelances professionnels / agences / power users\n\n📦 Contenu\nProjets illimités\nSupport prioritaire\nMise en avant du profil\nAccès aux analytics avancés\nAccès aux meilleures opportunités', 9999, 'oui']
    ];

    foreach ($packs as $pack) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `pack` (`id-pack`, `nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($pack);
    }
    echo "<p>✓ Packs created</p>";

    // Insert test abonnements
    $abonnements = [
        [1, 1, '2025-01-15', '2025-02-14', 'actif'],
        [2, 2, '2025-01-20', '2025-02-19', 'actif'],
        [3, 3, '2024-12-01', '2025-01-01', 'expiré'],
        [4, 4, '2025-01-10', '2025-02-09', 'actif'],
        [5, 5, '2025-01-25', '2025-02-24', 'actif']
    ];

    foreach ($abonnements as $abo) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($abo);
    }
    echo "<p>✓ Test abonnements created</p>";

    // Link abonnements to packs
    $abon_packs = [
        [1, 1],  // John Doe with Basic pack
        [2, 2],  // Jane Smith with Pro pack
        [1, 3],  // Bob Wilson with Basic pack (expired)
        [3, 4],  // Alice Brown with Premium pack
        [2, 5]   // Charlie Davis with Pro pack
    ];

    foreach ($abon_packs as $ap) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `abon-pack` (`id-pack`, `id-abonnement`) VALUES (?, ?)");
        $stmt->execute($ap);
    }
    echo "<p>✓ Pack-abonnement links created</p>";

    echo "<h3>✅ Test data setup complete!</h3>";
    echo "<p><a href='/view/front/abonnement.php'>Test abonnement page</a> | <a href='/view/back/dashboard_abonnements.php'>Test admin dashboard</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
