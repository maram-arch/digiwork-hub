<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
require_once __DIR__ . '/../../model/Candidature.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: offres.php');
    exit;
}

$id_offer = (int)($_POST['id_offer'] ?? 0);
$lettre   = trim($_POST['lettre_motivation'] ?? '');

if ($id_offer <= 0) {
    header('Location: offres.php?status=error&msg=' . urlencode('Offre invalide'));
    exit;
}

// ── Connexion ──
$dbTemp = Config::getConnexion();
$dbTemp->exec("SET FOREIGN_KEY_CHECKS=0");

// ── id_user : même logique que mes_candidatures.php ──
$id_user = (int)$dbTemp->query("SELECT id_user FROM user ORDER BY id_user ASC LIMIT 1")->fetchColumn();
if ($id_user <= 0) $id_user = 14;

// ── Vérifie si déjà candidaté ──
$check = $dbTemp->prepare(
    "SELECT COUNT(*) FROM condidateur WHERE id_user = :id_user AND id_offer = :id_offer"
);
$check->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
if ((int)$check->fetchColumn() > 0) {
    $dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");
    header('Location: offres.php?status=error&msg=' . urlencode('Vous avez déjà postulé à cette offre'));
    exit;
}

// ── Upload CV ──
$uploadDir = __DIR__ . '/assets/uploads/cv/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (empty($_FILES['cv']['name'])) {
    $dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");
    header('Location: offres.php?status=error&msg=' . urlencode('CV manquant'));
    exit;
}

$allowedExt = ['pdf', 'doc', 'docx'];
$fileExt    = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));

if (!in_array($fileExt, $allowedExt)) {
    $dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");
    header('Location: offres.php?status=error&msg=' . urlencode('Format de CV non accepté'));
    exit;
}
if ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
    $dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");
    header('Location: offres.php?status=error&msg=' . urlencode('CV trop volumineux (max 2Mo)'));
    exit;
}

$fileName = 'cv_' . $id_user . '_' . time() . '.' . $fileExt;
if (!move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $fileName)) {
    $dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");
    header('Location: offres.php?status=error&msg=' . urlencode("Erreur upload CV"));
    exit;
}

// ── Insertion ──
$candidature = new Candidature(
    id_user:  $id_user,
    id_offer: $id_offer,
    cv:       $fileName,
    Lettre:   $lettre,
    Date:     date('Y-m-d'),
    Statut:   'en attente'
);

$controller = new CandidatureController();
$success    = $controller->addCandidature($candidature);
$dbTemp->exec("SET FOREIGN_KEY_CHECKS=1");

if ($success) {
    header('Location: mes_candidatures.php?status=success&msg=' . urlencode('Candidature envoyée avec succès !'));
} else {
    if (file_exists($uploadDir . $fileName)) unlink($uploadDir . $fileName);
    header('Location: offres.php?status=error&msg=' . urlencode("Erreur lors de l'enregistrement"));
}
exit;