<?php
require_once('../model/Pack.php');

header('Content-Type: application/json');

$pack = new Pack();

if (isset($_GET['action']) && $_GET['action'] === 'getAll') {
    $packs = $pack->getAll()->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($packs);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Delete Pack (AJAX POST)
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $pack->delete($_POST['id']);
        echo json_encode(['status' => 'success', 'message' => 'Pack supprimé avec succès']);
        exit;
    }

    // Add Pack (AJAX POST)
    if ($_POST['action'] === 'add') {
        $pack->add(
            $_POST['nom'],
            floatval($_POST['prix']),
            $_POST['duree'],
            $_POST['description'],
            intval($_POST['nb']),
            $_POST['support']
        );
        echo json_encode(['status' => 'success', 'message' => 'Pack ajouté avec succès']);
        exit;
    }

    // Update Pack (AJAX POST)
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
        echo json_encode(['status' => 'success', 'message' => 'Pack modifié avec succès']);
        exit;
    }
}
// Support GET-based delete (admin link like PackController.php?delete=1&id=3)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pack->delete($id);

    // If request expects JSON, return JSON
    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Pack supprimé avec succès']);
        exit;
    }

    // Otherwise redirect back to admin UI
    header('Location: /view/back/dashboard_packs.php');
    exit;
}
?>
