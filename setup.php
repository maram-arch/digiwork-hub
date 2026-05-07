<?php
// DigiWork Hub Setup Script
echo "<h1>DigiWork Hub - Installation</h1>";

// Step 1: Database Setup
echo "<h2>1. Configuration de la base de données</h2>";

try {
    $pdo = new PDO("mysql:host=127.0.0.1", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `digiwork-hub`");
    echo "<p>✅ Base de données 'digiwork-hub' créée avec succès</p>";
    
    // Use the database
    $pdo->exec("USE `digiwork-hub`");
    
    // Import database structure
    if (file_exists('database.sql')) {
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
        echo "<p>✅ Structure de la base de données importée</p>";
    }
    
    // Import seed data
    if (file_exists('seed_admin.sql')) {
        $sql = file_get_contents('seed_admin.sql');
        $pdo->exec($sql);
        echo "<p>✅ Données administrateur importées</p>";
    }
    
    if (file_exists('seed_abonnement_data.sql')) {
        $sql = file_get_contents('seed_abonnement_data.sql');
        $pdo->exec($sql);
        echo "<p>✅ Données d'abonnement importées</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Erreur de base de données: " . $e->getMessage() . "</p>";
}

// Step 2: Check required files
echo "<h2>2. Vérification des fichiers requis</h2>";

$required_files = [
    'config/config.php',
    'controller/AbonnementController.php',
    'view/back/dashboard.php',
    'admin_login.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file - Trouvé</p>";
    } else {
        echo "<p>❌ $file - Manquant</p>";
    }
}

// Step 3: Access links
echo "<h2>3. Liens d'accès</h2>";
echo "<p><a href='index.html'>🏠 Page d'accueil</a></p>";
echo "<p><a href='admin_login.php'>🔑 Connexion Administrateur</a></p>";
echo "<p><a href='quick_login.php'>⚡ Connexion Rapide</a></p>";
echo "<p><a href='view/back/dashboard.php'>📊 Tableau de bord Admin</a></p>";

echo "<h2>Installation terminée!</h2>";
echo "<p>Le projet DigiWork Hub est maintenant prêt à être utilisé.</p>";
?>
