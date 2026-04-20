<?php
require_once __DIR__ . "/../model/Abonnement.php";

// Create an in-memory SQLite PDO to avoid touching user's MySQL
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create minimal schema matching what Abonnement expects
$pdo->exec("CREATE TABLE `user` (
    id_user INTEGER PRIMARY KEY,
    nom TEXT,
    tel TEXT,
    email TEXT
);");

$pdo->exec("CREATE TABLE `pack` (
    `id-pack` INTEGER PRIMARY KEY,
    `nom-pack` TEXT,
    `nb-proj-max` INTEGER
);");

$pdo->exec("CREATE TABLE `abonnement` (
    `id-abonnement` INTEGER PRIMARY KEY,
    `id-user` INTEGER,
    `date-deb` TEXT,
    `date-fin` TEXT,
    `status` TEXT
);");

$pdo->exec("CREATE TABLE `abon-pack` (
    `id-abon-pack` INTEGER PRIMARY KEY,
    `id-pack` INTEGER,
    `id-abonnement` INTEGER
);");

// Insert sample data
$pdo->exec("INSERT INTO `user` (id_user, nom, tel, email) VALUES (1, 'Alice', '0123456789', 'alice@example.com')");
$pdo->exec("INSERT INTO `pack` (`id-pack`, `nom-pack`, `nb-proj-max`) VALUES (1, 'Basic', 5)");
$pdo->exec("INSERT INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`) VALUES (1, 1, datetime('now'), datetime('now','+30 days'), 'actif')");
$pdo->exec("INSERT INTO `abon-pack` (`id-pack`, `id-abonnement`) VALUES (1, 1)");

// Inject $pdo into global scope (Abonnement methods use global $pdo)
$GLOBALS['pdo'] = $pdo;

$abo = new Abonnement();

try {
    echo "getAllAbonnements:\n";
    $all = $abo->getAllAbonnements();
    print_r($all);

    echo "\ngetByUser(1):\n";
    $byUser = $abo->getByUser(1);
    print_r($byUser);

    echo "\ngetActiveByUser(1):\n";
    $active = $abo->getActiveByUser(1);
    print_r($active);

    echo "\nCalling subscribe(1,1):\n";
    $newId = $abo->subscribe(1,1);
    var_export($newId);
    echo "\n\nAfter subscribe, getAllAbonnements:\n";
    print_r($abo->getAllAbonnements());

    echo "\nupdateExpiredStatus (should be 0): ";
    $aff = $abo->updateExpiredStatus();
    var_export($aff);
    echo "\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

?>
