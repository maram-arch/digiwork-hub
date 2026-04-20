<?php
session_start();

$action = $_GET['action'] ?? '';

if (in_array($action, ['list', 'add', 'edit', 'delete'])) {
    require_once __DIR__ . '/controller/PublicationController.php';
    $controller = new PublicationController();

    switch ($action) {
        case 'list':
            $controller->listPublications();
            break;
        case 'add':
            $controller->addPublication();
            break;
        case 'edit':
            $controller->editPublication();
            break;
        case 'delete':
            $controller->deletePublication();
            break;
    }
    exit();
}