// deleteCandidature.php (backoffice)
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listCandidatures.php');
    exit;
}

$id_user  = (int)($_POST['id_user']  ?? 0);
$id_offer = (int)($_POST['id_offer'] ?? 0);

if ($id_user && $id_offer) {
    $controller = new CandidatureController();
    $ok = $controller->deleteCandidatureAdmin($id_user, $id_offer); // ← méthode admin
    if ($ok) {
        header("Location: listCandidatures.php?status=success&msg=" . urlencode("Candidature supprimée avec succès."));
    } else {
        header("Location: listCandidatures.php?status=error&msg=" . urlencode("Erreur lors de la suppression."));
    }
} else {
    header("Location: listCandidatures.php?status=error&msg=" . urlencode("Données invalides."));
}
exit;