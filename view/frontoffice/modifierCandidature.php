<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mes_candidatures.php');
    exit;
}

$id_user  = (int)($_POST['id_user']  ?? 0);
$id_offer = (int)($_POST['id_offer'] ?? 0);
$lettre   = trim($_POST['lettre_motivation'] ?? '');

if ($id_user <= 0 || $id_offer <= 0 || strlen($lettre) < 10) {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Données invalides'));
    exit;
}

$controller  = new CandidatureController();
$candidature = $controller->getCandidatureById($id_user, $id_offer);

if (!$candidature) {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Candidature introuvable'));
    exit;
}

$statut = $candidature['Statut'] ?? '';
if (!in_array($statut, ['en attente', 'en_attente'])) {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Modification impossible : candidature déjà traitée'));
    exit;
}

/* ── Nouveau CV (optionnel) ── */
$cvFileName = $candidature['cv'];
$uploadDir  = __DIR__ . '/assets/uploads/cv/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!empty($_FILES['cv']['name'])) {
    $allowedExt = ['pdf', 'doc', 'docx'];
    $fileExt    = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExt)) {
        header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Format CV non accepté (PDF, DOC, DOCX)'));
        exit;
    }
    if ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
        header('Location: mes_candidatures.php?status=error&msg=' . urlencode('CV trop volumineux (max 2 Mo)'));
        exit;
    }

    $newFile = 'cv_' . $id_user . '_' . time() . '.' . $fileExt;
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $newFile)) {
        $oldPath = $uploadDir . $candidature['cv'];
        if (file_exists($oldPath)) unlink($oldPath);
        $cvFileName = $newFile;
    }
}

$success = $controller->updateCandidature($id_user, $id_offer, $cvFileName, $lettre);

if ($success) {
    header('Location: mes_candidatures.php?status=success&msg=' . urlencode('Candidature mise à jour !'));
} else {
    header('Location: mes_candidatures.php?status=error&msg=' . urlencode('Erreur lors de la mise à jour'));
}
exit;