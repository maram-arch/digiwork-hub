<?php
session_start();

require_once __DIR__ . '/../../controller/CandidatureController.php';

$sessionUser = (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);
$idUser = (int)($_GET['id_user'] ?? 0);
$idOffer = (int)($_GET['id_offer'] ?? 0);

$status = 'error';
$message = 'Suppression impossible.';

if ($sessionUser > 0 && $idUser === $sessionUser && $idOffer > 0) {
    $ok = (new CandidatureController())->deleteCandidature($idUser, $idOffer);
    $status = $ok ? 'success' : 'error';
    $message = $ok ? 'Candidature supprimee avec succes.' : 'Erreur lors de la suppression.';
}

header('Location: mes_candidatures.php?status=' . urlencode($status) . '&msg=' . urlencode($message));
exit;
