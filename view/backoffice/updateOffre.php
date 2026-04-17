<?php
require_once __DIR__ . '/../../controller/OffreController.php';
require_once __DIR__ . '/../../model/Offre.php';
 
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}
 
// ── Validation ───────────────────────────────────────────────
$errors = [];
if (empty($_POST["id_offer"]))      $errors[] = "ID offre manquant.";
if (empty($_POST["titre"]))         $errors[] = "Le titre est obligatoire.";
if (empty($_POST["description"]))   $errors[] = "La description est obligatoire.";
if (empty($_POST["competences"]))   $errors[] = "Les compétences sont obligatoires.";
if (empty($_POST["date_limite"]))   $errors[] = "La date limite est obligatoire.";
if (empty($_POST["adresse"]))       $errors[] = "L'adresse est obligatoire.";
if (empty($_POST["type"]))          $errors[] = "Le type de contrat est obligatoire.";
if (empty($_POST["id_entreprise"])) $errors[] = "L'ID entreprise est obligatoire.";

if (empty($errors)) {
    $date_limite = trim($_POST["date_limite"]);
    $dateObj = DateTime::createFromFormat('Y-m-d', $date_limite);
    $dateValid = $dateObj && $dateObj->format('Y-m-d') === $date_limite;
    $today = new DateTime('today');

    if (!$dateValid) {
        $errors[] = "La date limite doit être au format YYYY-MM-DD.";
    } elseif ($dateObj < $today) {
        $errors[] = "La date limite doit être aujourd'hui ou dans le futur.";
    }

    $id_entreprise = trim($_POST["id_entreprise"]);
    if (!preg_match('/^[0-9]{1,8}$/', $id_entreprise)) {
        $errors[] = "L'ID entreprise doit contenir uniquement des chiffres et ne peut pas dépasser 8 caractères.";
    }
}

if (!empty($errors)) {
    header("Location: index.php?status=error&msg=" . urlencode(implode(" | ", $errors)));
    exit;
}
 
// ── Nettoyage & instanciation du modèle ─────────────────────
$id = intval($_POST["id_offer"]);
 
$offre = new Offre(
    $id,
    htmlspecialchars(trim($_POST["titre"])),
    htmlspecialchars(trim($_POST["description"])),
    htmlspecialchars(trim($_POST["competences"])),
    $_POST["date_limite"],
    htmlspecialchars(trim($_POST["adresse"])),
    htmlspecialchars(trim($_POST["type"])),
    intval($_POST["id_entreprise"])
);
 
// ── Appel controller ─────────────────────────────────────────
try {
    $controller = new OffreController();
    $controller->updateOffre($offre, $id);
 
    header("Location: index.php?status=success&msg=" . urlencode("Offre mise à jour avec succès !"));
    exit;
} catch (Exception $e) {
    header("Location: index.php?status=error&msg=" . urlencode("Erreur base de données : " . $e->getMessage()));
    exit;
}
?>