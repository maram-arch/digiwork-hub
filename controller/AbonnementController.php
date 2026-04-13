<?php
session_start();
require_once('../model/Abonnement.php');

$abo = new Abonnement();

// Return JSON for API consumers
// Return JSON for API consumers: all abonnements (admin) or per-user via getMine
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getAll') {
        header('Content-Type: application/json');
        $abonnements = $abo->getAllAbonnements();
        echo json_encode($abonnements);
        exit;
    }

    if ($_GET['action'] === 'getMine') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }
        $userId = intval($_SESSION['user_id']);
        $abonnements = $abo->getByUser($userId);
        echo json_encode($abonnements);
        exit;
    }
}

// Handle POST requests (both AJAX and normal form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Admin Deletion (AJAX or form)
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $abo->delete($_POST['id']);

        // If AJAX, return JSON
        if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement supprimé avec succès']);
            exit;
        }

        // Otherwise redirect back to admin page with a flash
        $_SESSION['flash'] = 'Abonnement supprimé avec succès';
        header('Location: /view/back/dashboard_abonnements.php');
        exit;
    }

    // Subscribe action (AJAX POST or normal form POST)
    if ($_POST['action'] === 'subscribe') {
        // Attempt to use session user id if available
        $id_user = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1; // fallback to 1 for now
        $pack_id = intval($_POST['pack_id']);

        try {
            $abo->subscribe($id_user, $pack_id);

            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Abonnement réussi !']);
                exit;
            }

            // Non-AJAX: set flash and redirect to front abonnement page
            $_SESSION['flash'] = 'Abonnement réussi !';
            header('Location: /view/front/abonnement.php');
            exit;
        } catch (PDOException $e) {
            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données.']);
                exit;
            }

            $_SESSION['flash'] = 'Erreur lors de la création de l\'abonnement.';
            header('Location: /view/front/packs.php');
            exit;
        }
    }
}
?>
