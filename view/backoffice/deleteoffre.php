<?php
require_once __DIR__ . '/../../config/config.php';
 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $db   = Config::getconnexion();
        $stmt = $db->prepare("DELETE FROM offre WHERE id_offer = ?");
        $stmt->execute([intval($_GET['id'])]);
 
        header("Location: listOffres.php?status=success&msg=" . urlencode("Offre supprimée avec succès !"));
        exit;
 
    } catch (Exception $e) {
        header("Location: listOffres.php?status=error&msg=" . urlencode("Erreur lors de la suppression."));
        exit;
    }
}
 
header("Location: listOffres.php");
exit;
 