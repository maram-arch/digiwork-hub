<?php
session_start();
require_once('config/config.php');
require_once('model/Abonnement.php');
require_once('model/Pack.php');

echo "<h2 style='color: #00A651;'>Subscription Test</h2>";
echo "<style>body { background-color: #FFFFFF !important; }</style>";

// Test 1: Check if user is logged in
echo "<h3>Session Status:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Email: " . ($_SESSION['user_email'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Test 2: Check existing abonnements
echo "<h3>Current Abonnements in Database:</h3>";
$abo = new Abonnement();
try {
    $allAbos = $abo->getAll()->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Pack</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    foreach ($allAbos as $abo) {
        echo "<tr>";
        echo "<td>{$abo['id-abonnement']}</td>";
        echo "<td>{$abo['id-user']}</td>";
        echo "<td>{$abo['nom-pack']}</td>";
        echo "<td>{$abo['date-deb']}</td>";
        echo "<td>{$abo['date-fin']}</td>";
        echo "<td>{$abo['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error getting abonnements: " . $e->getMessage() . "</p>";
}

// Test 3: Test getMine method if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<h3>My Abonnements (getMine method):</h3>";
    try {
        $myAbos = $abo->getByUser($_SESSION['user_id']);
        echo "<pre>";
        print_r($myAbos);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error getting my abonnements: " . $e->getMessage() . "</p>";
    }
}

// Test 4: Test creating a subscription
echo "<h3>Test Subscription Creation:</h3>";
if (isset($_SESSION['user_id'])) {
    try {
        $packModel = new Pack();
        $packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($packs)) {
            $firstPack = $packs[0];
            echo "<p>Attempting to subscribe user {$_SESSION['user_id']} to pack {$firstPack['id-pack']} ({$firstPack['nom-pack']})</p>";
            
            $abId = $abo->subscribe($_SESSION['user_id'], $firstPack['id-pack']);
            if ($abId) {
                echo "<p style='color: green;'>✅ Subscription created with ID: $abId</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to create subscription</p>";
            }
        } else {
            echo "<p>No packs available in database</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error creating subscription: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ User not logged in - cannot test subscription</p>";
}

echo "<hr>";
echo "<p><a href='/admin_quick_login.php'>Login as Admin</a> | <a href='/quick_login.php'>Login as User</a></p>";
?>
