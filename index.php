<<<<<<< HEAD
<li class="sidebar-item">
    <a href="listPublications.php" class="sidebar-link">
        <i data-feather="message-square"></i> 
        <span>Gestion Forum</span>
    </a>
</li>
=======
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
>>>>>>> 6b3b218dc29227adaacddd025b9d802292528038
