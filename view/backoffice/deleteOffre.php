<?php
require_once __DIR__ . '/../../controller/OffreController.php';

$id = (int)($_GET['id'] ?? 0);
$status = 'error';
$message = 'Suppression impossible.';

if ($id > 0) {
    (new OffreController())->deleteOffre($id);
    $status = 'success';
    $message = 'Offre supprimee avec succes.';
}

header('Location: listOffres.php?status=' . urlencode($status) . '&msg=' . urlencode($message));
exit;
