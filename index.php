<?php
session_start();

$action = $_GET['action'] ?? '';
$module = $_GET['module'] ?? '';

// ── Gestion Forum / Publications ────────────────────────────────────────────
if (in_array($action, ['list', 'add', 'edit', 'delete']) && ($module === 'forum' || $module === '')) {
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

// ── Gestion Forum (ForumController) ─────────────────────────────────────────
if ($module === 'forumCtrl') {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/controller/forumController.php';
    $db = Config::getConnexion();
    $controller = new ForumController($db);

    switch ($action) {
        case 'list':   $controller->index();  break;
        case 'add':    $controller->add();    break;
        case 'edit':   $controller->edit();   break;
        case 'delete': $controller->delete(); break;
        default:       $controller->index();  break;
    }
    exit();
}

// ── Autres modules (User, Event, Pack, Projet, Offre) ───────────────────────
// Les collègues ajoutent leurs routes ici selon leur module
// Exemple structure :
// if ($module === 'user') { require_once ... }
// if ($module === 'event') { require_once ... }
// if ($module === 'pack') { require_once ... }
// if ($module === 'projet') { require_once ... }

// ── Page d'accueil par défaut ────────────────────────────────────────────────
require_once __DIR__ . '/view/frontoffice/index.php';