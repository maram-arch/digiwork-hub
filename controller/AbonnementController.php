<?php
session_start();
require_once('../model/Abonnement.php');
require_once('../model/Pack.php');

$abo = new Abonnement();
$packModel = new Pack();

function redirectTo(string $path): void {
    header('Location: ' . $path);
    exit;
}

function jsonResponse(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

// Return JSON for API consumers
// Return JSON for API consumers: all abonnements (admin) or per-user via getMine
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getAll') {
        // Admin only
        if (($_SESSION['role'] ?? '') !== 'admin') {
            jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
        }
        $abo->updateExpiredStatus();
        $abonnements = $abo->getAllAbonnements();
        jsonResponse($abonnements);
    }

    if ($_GET['action'] === 'getMine') {
        $abo->updateExpiredStatus();
        if (!isset($_SESSION['user_id'])) {
            jsonResponse([]);
        }
        $userId = intval($_SESSION['user_id']);
        $abonnements = $abo->getByUser($userId);
        jsonResponse($abonnements);
    }
}

// Handle POST requests (both AJAX and normal form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Admin Deletion (AJAX or form)
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
        }
        $abo->delete($_POST['id']);

        // If AJAX, return JSON
        if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            jsonResponse(['status' => 'success', 'message' => 'Abonnement supprimé avec succès']);
        }

        // Otherwise redirect back to admin page with a flash
        $_SESSION['flash'] = 'Abonnement supprimé avec succès';
        redirectTo('/back/dashboard_abonnements.php');
    }

    // Admin status update (deactivate/expire/activate)
    if ($_POST['action'] === 'setStatus' && isset($_POST['id']) && isset($_POST['status'])) {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
        }
        $ok = $abo->setStatus((int)$_POST['id'], (string)$_POST['status']);
        if (!$ok) {
            jsonResponse(['status' => 'error', 'message' => 'Statut invalide'], 400);
        }
        jsonResponse(['status' => 'success', 'message' => 'Statut mis à jour']);
    }

    // Subscribe action (AJAX POST or normal form POST)
    if ($_POST['action'] === 'subscribe') {
        $userId = $_SESSION['user_id'] ?? null;
        $packId = intval($_POST['pack_id']);
        if (!$userId) {
            if (isset($_POST['ajax'])) {
                jsonResponse(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner.'], 401);
            }

            $_SESSION['flash'] = 'Vous devez être connecté pour vous abonner.';
            redirectTo('/front/login.php');
        }

        // Validate pack exists
        $pack = $packModel->getById($packId);
        if (!$pack) {
            if (isset($_POST['ajax'])) {
                jsonResponse(['status' => 'error', 'message' => 'Pack introuvable.'], 400);
            }
            $_SESSION['flash'] = 'Pack introuvable.';
            redirectTo('/front/packs.php');
        }

        $abId = $abo->subscribe($userId, $packId);
        // If subscription creation failed, return an error for AJAX and non-AJAX flows
        if ($abId === false) {
            if (isset($_POST['ajax'])) {
                jsonResponse(['status' => 'error', 'message' => 'Abonnement déjà actif ou création impossible.'], 400);
            }

            $_SESSION['flash'] = 'Abonnement déjà actif ou création impossible.';
            redirectTo('/front/abonnement.php');
        }

        if (isset($_POST['ajax'])) {
            jsonResponse(['status' => 'success', 'message' => 'Abonnement créé avec succès', 'abonnement_id' => $abId]);
        }

        $_SESSION['flash'] = 'Abonnement créé avec succès';
        redirectTo('/front/abonnement.php');
    }
}
?>
