<?php
require_once('../model/Pack.php');

session_start();

$pack = new Pack();

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

// Return JSON list if requested
if (isset($_GET['action']) && $_GET['action'] === 'getAll') {
    header('Content-Type: application/json');
    $packs = $pack->getAll()->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($packs);
    exit;
}

// Handle POST form submissions (Add / Update / Delete via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Back-office CRUD should be admin-only
    denyIfNotAdmin();

    // Delete Pack (form submit)
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $pack->delete(intval($_POST['id']));
        // If AJAX, return JSON
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Pack supprimé avec succès']);
            exit;
        }

        $_SESSION['flash'] = 'Pack supprimé avec succès';
        header('Location: /view/back/dashboard_packs.php');
        exit;
    }

    // Add Pack
    if ($_POST['action'] === 'add') {
        $pack->add(
            $_POST['nom'],
            floatval($_POST['prix']),
            $_POST['duree'],
            $_POST['description'],
            intval($_POST['nb']),
            $_POST['support']
        );
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Pack ajouté avec succès']);
            exit;
        }

        $_SESSION['flash'] = 'Pack ajouté avec succès';
        header('Location: /view/back/dashboard_packs.php');
        exit;
    }

    // Update Pack
    if ($_POST['action'] === 'update') {
        $pack->update(
            $_POST['id-pack'],
            $_POST['nom'],
            floatval($_POST['prix']),
            $_POST['duree'],
            $_POST['description'],
            intval($_POST['nb']),
            $_POST['support']
        );
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Pack modifié avec succès']);
            exit;
        }

        $_SESSION['flash'] = 'Pack modifié avec succès';
        header('Location: /view/back/dashboard_packs.php');
        exit;
    }
}

// Support GET-based delete (admin link like PackController.php?delete=1&id=3)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    denyIfNotAdmin();
    $id = intval($_GET['id']);
    $pack->delete($id);
    $_SESSION['flash'] = 'Pack supprimé avec succès';
    header('Location: /view/back/dashboard_packs.php');
    exit;
}
// Default: redirect to packs dashboard
header('Location: /view/back/dashboard_packs.php');
exit;
