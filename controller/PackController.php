<?php
require_once('../model/Pack.php');

$pack = new Pack();

// Front-Office API Route via Fetch (kept for compatibility)
if (isset($_GET['action']) && $_GET['action'] === 'getAll') {
    $packs = $pack->getAll()->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($packs);
    exit;
}

// Admin Deletion (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pack->delete($_GET['id']);
    header('Location: ../view/back/dashboard_packs.php');
    exit;
}

// Add Pack (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $pack->add(
        $_POST['nom'],
        floatval($_POST['prix']),
        $_POST['duree'],
        $_POST['description'],
        intval($_POST['nb']),
        $_POST['support']
    );
    header('Location: ../view/back/dashboard_packs.php');
    exit;
}

// Update Pack (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $pack->update(
        $_POST['id-pack'],
        $_POST['nom'],
        floatval($_POST['prix']),
        $_POST['duree'],
        $_POST['description'],
        intval($_POST['nb']),
        $_POST['support']
    );
    header('Location: ../view/back/dashboard_packs.php');
    exit;
}
?>
