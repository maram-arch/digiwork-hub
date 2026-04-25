<?php
session_start();
require_once('../model/Abonnement.php');

$abo = new Abonnement();

function isAdmin(): bool {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function wantsJson(): bool {
    if (isset($_POST['ajax'])) return true;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return is_string($accept) && strpos($accept, 'application/json') !== false;
}

function denyIfNotAdmin(): void {
    if (isAdmin()) return;
    if (wantsJson()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Accès refusé (admin requis).']);
        exit;
    }
    $_SESSION['flash'] = 'Accès refusé (admin requis).';
    header('Location: /view/front/login.php');
    exit;
}

// Return JSON for API consumers
// Return JSON for API consumers: all abonnements (admin) or per-user via getMine
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getAll') {
        denyIfNotAdmin();
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
        denyIfNotAdmin();
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
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès', 'abonnement_id' => $abId]);
            exit;
        }

        $_SESSION['flash'] = 'Abonnement créé avec succès';
        header('Location: /view/front/abonnement.php');
        exit;
    }

    // Update action (AJAX POST only)
    if ($_POST['action'] === 'update' && isset($_POST['id'])) {
        denyIfNotAdmin();
        
        $id = intval($_POST['id']);
        $status = $_POST['status'] ?? '';
        $dateFin = $_POST['date_fin'] ?? '';
        
        $result = $abo->update($id, $status, $dateFin);
        
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Abonnement mis à jour avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }
}

// Default: redirect
header('Location: /view/front/packs.php');
exit;
?>
