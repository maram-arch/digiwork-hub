<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listCandidatures.php');
    exit;
}

$id_user  = (int)($_POST['id_user']  ?? 0);
$id_offer = (int)($_POST['id_offer'] ?? 0);
$statut   = $_POST['statut'] ?? '';
$allowed  = ['accepte', 'refuse', 'en_attente'];

if ($id_user && $id_offer && in_array($statut, $allowed)) {
    $controller = new CandidatureController();
    $ok = $controller->updateStatut($id_user, $id_offer, $statut);
    if ($ok) {
        $label = $statut === 'accepte' ? 'Candidature acceptée.' : 'Candidature refusée.';
        header("Location: listCandidatures.php?status=success&msg=" . urlencode($label));
    } else {
        header("Location: listCandidatures.php?status=error&msg=" . urlencode("Erreur lors de la mise à jour."));
    }
} else {
    header("Location: listCandidatures.php?status=error&msg=" . urlencode("Données invalides."));
}
exit;