<?php
session_start();

// Set admin session for testing
$_SESSION['role'] = 'admin';
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin';

echo "<h2>Admin Session Created</h2>";
echo "<p>✅ Admin session has been set</p>";
echo "<p><a href='/view/back/dashboard_abonnements.php'>Go to Admin Dashboard</a></p>";
echo "<p><a href='/controller/AbonnementController.php?action=getAll'>Test API Endpoint</a></p>";

// Test the API endpoint directly
echo "<h3>Testing API Endpoint:</h3>";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/controller/AbonnementController.php?action=getAll');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && is_array($data)) {
            echo "<p>✅ API returned " . count($data) . " abonnements</p>";
            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p>❌ API returned invalid response: " . htmlspecialchars($response) . "</p>";
        }
    } else {
        echo "<p>❌ Failed to call API</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
