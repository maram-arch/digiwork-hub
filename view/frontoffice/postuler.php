<?php
session_start();

require_once __DIR__ . '/../../controller/CandidatureController.php';

function redirectOffres(string $status, string $message): void
{
    header('Location: offres.php?status=' . urlencode($status) . '&msg=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectOffres('error', 'Requete invalide.');
}

$idUser = (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? ($_POST['id_user'] ?? 0));
$idOffer = (int)($_POST['id_offer'] ?? 0);
$lettre = trim((string)($_POST['lettre_motivation'] ?? ''));

if ($idUser <= 0) {
    redirectOffres('error', 'Vous devez vous connecter avant de postuler.');
}

if ($idOffer <= 0 || $lettre === '') {
    redirectOffres('error', 'Veuillez remplir tous les champs obligatoires.');
}

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    redirectOffres('error', 'Veuillez joindre votre CV.');
}

$file = $_FILES['cv'];
$maxSize = 2 * 1024 * 1024;
$extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'doc', 'docx'];

if ($file['size'] > $maxSize || !in_array($extension, $allowedExtensions, true)) {
    redirectOffres('error', 'CV invalide : format PDF/DOC/DOCX et taille max 2 Mo.');
}

$uploadDir = __DIR__ . '/assets/uploads/cv';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$cvName = 'cv_' . $idUser . '_' . time() . '.' . $extension;
$target = $uploadDir . DIRECTORY_SEPARATOR . $cvName;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    redirectOffres('error', "Impossible d'enregistrer le CV.");
}

$candidature = new Candidature($idUser, $idOffer, $cvName, $lettre);
$ok = (new CandidatureController())->addCandidature($candidature);

redirectOffres($ok ? 'success' : 'error', $ok ? 'Candidature envoyee avec succes.' : "Erreur lors de l'envoi de la candidature.");
