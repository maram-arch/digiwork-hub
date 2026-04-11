<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;
}

$action = $_GET['action'] ?? '';

$actions_valides = ['list', 'add', 'edit', 'delete', 'like', 'voir'];

if (in_array($action, $actions_valides)) {
    require_once __DIR__ . '/controller/PublicationController.php';
    $controller = new PublicationController();

    switch ($action) {
        case 'list':   $controller->listPublications();  break;
        case 'add':    $controller->addPublication();    break;
        case 'edit':   $controller->editPublication();   break;
        case 'delete': $controller->deletePublication(); break;
        case 'like':   $controller->likePublication();   break;
        case 'voir':   $controller->voirPublication();   break;
    }
    exit();
}

header("Location: index.php?action=list");
exit();