<?php
require_once __DIR__ . '/../../controller/PublicationController.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $controller = new PublicationController();
    $controller->deletePublication($id);
    header("Location: index.php?status=success&msg=" . urlencode("Publication supprimée"));
    exit;
} else {
    header("Location: index.php?status=error&msg=" . urlencode("ID invalide"));
    exit;
}