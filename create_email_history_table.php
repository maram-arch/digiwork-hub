<?php
require_once('config/config.php');

$sql = "CREATE TABLE IF NOT EXISTS `email_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `recipient_count` int(11) NOT NULL,
    `success_count` int(11) NOT NULL,
    `failed_count` int(11) NOT NULL,
    `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sent_by` varchar(50) DEFAULT 'admin',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $pdo->exec($sql);
    echo "Table 'email_history' created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
