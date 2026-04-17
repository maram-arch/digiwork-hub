<?php
require_once __DIR__ . '/../../controller/OffreController.php';
 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $controller = new OffreController();
        $controller->deleteOffre(intval($_GET['id']));
 
        header("Location: index.php?status=success&msg=" . urlencode("Offre supprimée avec succès !"));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?status=error&msg=" . urlencode("Erreur lors de la suppression."));
        exit;
    }
}
 
header("Location: index.php");
exit;
?>