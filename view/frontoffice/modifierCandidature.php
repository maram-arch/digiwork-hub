<?php
session_start();

require_once __DIR__ . '/../../controller/CandidatureController.php';

function redirectCandidatures(string $status, string $message): void
{
    header('Location: mes_candidatures.php?status=' . urlencode($status) . '&msg=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectCandidatures('error', 'Requete invalide.');
}

$sessionUser = (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);
$idUser = (int)($_POST['id_user'] ?? 0);
$idOffer = (int)($_POST['id_offer'] ?? 0);
$lettre = trim((string)($_POST['lettre_motivation'] ?? ''));

if ($sessionUser <= 0 || $idUser !== $sessionUser || $idOffer <= 0 || $lettre === '') {
    redirectCandidatures('error', 'Donnees invalides.');
}

$controller = new CandidatureController();
$current = $controller->getCandidatureById($idUser, $idOffer);
if (!$current) {
    redirectCandidatures('error', 'Candidature introuvable.');
}

$cvName = (string)($current['cv'] ?? '');
if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cv'];
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if ($file['size'] > 2 * 1024 * 1024 || !in_array($extension, ['pdf', 'doc', 'docx'], true)) {
        redirectCandidatures('error', 'CV invalide : format PDF/DOC/DOCX et taille max 2 Mo.');
    }

    $uploadDir = __DIR__ . '/assets/uploads/cv';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $cvName = 'cv_' . $idUser . '_' . time() . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $cvName)) {
        redirectCandidatures('error', "Impossible d'enregistrer le CV.");
    }
}

$ok = $controller->updateCandidature($idUser, $idOffer, $cvName, $lettre);
redirectCandidatures($ok ? 'success' : 'error', $ok ? 'Candidature modifiee avec succes.' : 'Erreur lors de la modification.');
