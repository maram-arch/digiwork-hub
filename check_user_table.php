<?php
require_once(__DIR__ . "/config/config.php");

echo "<h2>User Table Structure</h2>";

try {
    // Get table structure
    $stmt = $pdo->query("DESCRIBE `user`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Columns in user table:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get sample data
    echo "<h3>Sample user data:</h3>";
    $stmt = $pdo->query("SELECT * FROM `user` LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr>";
    foreach ($columns as $col) {
        echo "<th>{$col['Field']}</th>";
    }
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<td>" . ($user[$col['Field']] ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
