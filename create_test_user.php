<?php
require_once('config/config.php');

try {
    // Get existing users for testing
    $stmt = $pdo->query("SELECT id_user, email FROM user LIMIT 3");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2 style='color: #00A651;'>Available test users:</h2>";
echo "<style>body { background-color: #FFFFFF !important; }</style>";
    foreach ($users as $user) {
        echo "ID: {$user['id_user']}, Email: {$user['email']}\n";
    }
    
    // Create a simple login script to set session
    echo "\nUse these credentials for testing:\n";
    echo "Admin: admin@gmail.com / 12345678\n";
    echo "Regular: jane.smith@email.com / password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
