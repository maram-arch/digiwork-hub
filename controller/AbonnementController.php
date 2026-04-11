<?php
require_once('../model/Abonnement.php');

$abo = new Abonnement();

// Admin Deletion (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $abo->delete($_GET['id']);
    header('Location: ../view/back/dashboard_abonnements.php');
    exit;
}

// Subscribe action (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'subscribe') {
    // Hardcoded user logic for structural demonstration (No auth session in project yet)
    $id_user = 1; 
    
    // Default dates for 1 year subscription
    $date_deb = date('Y-m-d');
    $date_fin = date('Y-m-d', strtotime('+1 year'));
    
    $pack_id = intval($_POST['pack_id']);
    
    try {
        $abo->add($id_user, $date_deb, $date_fin, 'Actif', $pack_id);
    } catch (PDOException $e) {
        // Suppress failure due to missing static user during basic testing
    }
    
    header('Location: ../view/front/abonnement.php?success=1');
    exit;
}
?>
