<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

session_start();

$id_user  = (int)($_GET['id_user']  ?? 0);
$id_offer = (int)($_GET['id_offer'] ?? 0);

if ($id_user <= 0 || $id_offer <= 0) {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Données invalides'));
    exit;
}

$controller  = new CandidatureController();
$candidature = $controller->getCandidatureById($id_user, $id_offer);

/* ── Supprimer le fichier CV ── */
if ($candidature && !empty($candidature['cv'])) {
    $cvPath = __DIR__ . '/assets/uploads/cv/' . $candidature['cv'];
    if (file_exists($cvPath)) unlink($cvPath);
}

$success = $controller->deleteCandidature($id_user, $id_offer);

if ($success) {
    header('Location: mes_candidatures.php?status=success&msg=' . urlencode('Candidature supprimée'));
} else {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Suppression impossible'));
}
exit;