<?php
require_once(__DIR__ . "/config/config.php");
require_once(__DIR__ . "/model/Abonnement.php");

echo "<h2>Testing Abonnement Functionality</h2>";

$abo = new Abonnement();

try {
    // Test 1: Create a new subscription
    echo "<h3>1. Testing subscription creation...</h3>";
    $testUserId = 1; // John Doe
    $testPackId = 2; // PRO Pack
    
    $newAbonnementId = $abo->subscribe($testUserId, $testPackId);
    if ($newAbonnementId) {
        echo "<p>✅ Successfully created abonnement ID: $newAbonnementId</p>";
    } else {
        echo "<p>❌ Failed to create abonnement</p>";
    }
    
    // Test 2: Get all abonnements
    echo "<h3>2. Testing getAllAbonnements...</h3>";
    $allAbos = $abo->getAllAbonnements();
    echo "<p>✅ Found " . count($allAbos) . " abonnements total</p>";
    
    // Test 3: Get abonnements by user
    echo "<h3>3. Testing getByUser...</h3>";
    $userAbos = $abo->getByUser($testUserId);
    echo "<p>✅ User $testUserId has " . count($userAbos) . " abonnements</p>";
    
    // Test 4: Update abonnement status
    echo "<h3>4. Testing update functionality...</h3>";
    if ($newAbonnementId) {
        $updateResult = $abo->update($newAbonnementId, 'expiré', '2025-03-01');
        if ($updateResult) {
            echo "<p>✅ Successfully updated abonnement $newAbonnementId</p>";
        } else {
            echo "<p>❌ Failed to update abonnement</p>";
        }
    }
    
    // Test 5: Delete abonnement
    echo "<h3>5. Testing delete functionality...</h3>";
    if ($newAbonnementId) {
        $abo->delete($newAbonnementId);
        echo "<p>✅ Successfully deleted test abonnement $newAbonnementId</p>";
    }
    
    // Test 6: Check expired status update
    echo "<h3>6. Testing expired status update...</h3>";
    $updatedCount = $abo->updateExpiredStatus();
    echo "<p>✅ Updated $updatedCount expired abonnements</p>";
    
    echo "<h3>✅ All tests completed successfully!</h3>";
    
    // Show current abonnements
    echo "<h3>Current Abonnements:</h3>";
    $currentAbos = $abo->getAllAbonnements();
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User</th><th>Pack</th><th>Date Début</th><th>Date Fin</th><th>Status</th></tr>";
    
    foreach ($currentAbos as $abo) {
        echo "<tr>";
        echo "<td>{$abo['id-abonnement']}</td>";
        echo "<td>{$abo['nom']} ({$abo['tel']})</td>";
        echo "<td>{$abo['nom-pack']}</td>";
        echo "<td>{$abo['date-deb']}</td>";
        echo "<td>{$abo['date-fin']}</td>";
        echo "<td>{$abo['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Links:</h3>";
echo "<p><a href='/view/front/abonnement.php'>Front Abonnement Page</a></p>";
echo "<p><a href='/view/front/packs.php'>Front Packs Page</a></p>";
echo "<p><a href='/view/back/dashboard_abonnements.php'>Admin Dashboard</a></p>";
echo "<p><a href='setup_test_data.php'>Setup Test Data</a></p>";
?>
