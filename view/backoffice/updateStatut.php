<?php
require_once __DIR__ . '/../../controller/CandidatureController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUser = (int)($_POST['id_user'] ?? 0);
    $idOffer = (int)($_POST['id_offer'] ?? 0);
    $statut = trim((string)($_POST['statut'] ?? ''));

    if ($idUser > 0 && $idOffer > 0 && in_array($statut, ['accepte', 'refuse', 'en attente'], true)) {
        (new CandidatureController())->updateStatut($idUser, $idOffer, $statut);
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'listOffres.php'));
exit;
