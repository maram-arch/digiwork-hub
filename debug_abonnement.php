<?php
session_start();
require_once(__DIR__ . "/config/config.php");
require_once(__DIR__ . "/model/Abonnement.php");

echo "<h2>Debug Abonnement Data</h2>";

// Check session
echo "<h3>Session Info:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Try to get all abonnements directly
echo "<h3>Direct Database Query:</h3>";
try {
    $abo = new Abonnement();
    $allAbos = $abo->getAllAbonnements();
    echo "<p>Found " . count($allAbos) . " abonnements</p>";
    
    if (count($allAbos) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>User</th><th>Pack</th><th>Status</th></tr>";
        foreach ($allAbos as $abo) {
            echo "<tr>";
            echo "<td>{$abo['id-abonnement']}</td>";
            echo "<td>{$abo['nom']}</td>";
            echo "<td>{$abo['nom-pack']}</td>";
            echo "<td>{$abo['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test the controller endpoint
echo "<h3>Test Controller Endpoint:</h3>";
echo "<p>Testing <a href='/controller/AbonnementController.php?action=getAll'>getAll endpoint</a></p>";

// Set admin session for testing
$_SESSION['role'] = 'admin';
$_SESSION['user_id'] = 1;
echo "<p>Set admin session. <a href='debug_abonnement.php'>Reload</a> to test with admin rights.</p>";
?>
