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
        $userId = $_SESSION['user_id'] ?? null;
        $packId = intval($_POST['pack_id']);
        if (!$userId) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner.']);
                exit;
            }

            $_SESSION['flash'] = 'Vous devez être connecté pour vous abonner.';
            header('Location: /view/front/login.php');
            exit;
        }

        $abId = $abo->subscribe($userId, $packId);
        // If subscription creation failed, return an error for AJAX and non-AJAX flows
        if ($abId === false) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Impossible de créer l\'abonnement. Veuillez réessayer plus tard.']);
                exit;
            }

            $_SESSION['flash'] = 'Impossible de créer l\'abonnement. Veuillez réessayer plus tard.';
            header('Location: /view/front/abonnement.php');
            exit;
        }

        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès', 'abonnement_id' => $abId]);
            exit;
        }

        $_SESSION['flash'] = 'Abonnement créé avec succès';
        header('Location: /view/front/abonnement.php');
        exit;
    }
}
?>
