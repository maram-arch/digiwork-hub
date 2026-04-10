<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/OffreController.php';
require_once __DIR__ . '/../../model/Offre.php';
 
// Vérifier que la requête vient bien d'un formulaire POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listOffres.php");
    exit;
}
 
$errors = [];
 
// Validation des champs
if (empty($_POST["id_offer"]))       $errors[] = "ID offre manquant.";
if (empty($_POST["titre"]))          $errors[] = "Le titre est obligatoire.";
if (empty($_POST["description"]))    $errors[] = "La description est obligatoire.";
if (empty($_POST["competences"]))    $errors[] = "Les compétences sont obligatoires.";
if (empty($_POST["date_limite"]))    $errors[] = "La date limite est obligatoire.";
if (empty($_POST["adresse"]))        $errors[] = "L'adresse est obligatoire.";
if (empty($_POST["type"]))           $errors[] = "Le type de contrat est obligatoire.";
if (empty($_POST["id_entreprise"]))  $errors[] = "L'ID entreprise est obligatoire.";
 
if (!empty($errors)) {
    // Redirection avec message d'erreur encodé dans l'URL
    $msg = urlencode(implode(" | ", $errors));
    header("Location: listOffres.php?status=error&msg=" . $msg);
    exit;
}
 
// Récupération et nettoyage des données
$id            = intval($_POST["id_offer"]);
$titre         = htmlspecialchars(trim($_POST["titre"]));
$description   = htmlspecialchars(trim($_POST["description"]));
$competences   = htmlspecialchars(trim($_POST["competences"]));
$date_limite   = $_POST["date_limite"];
$adresse       = htmlspecialchars(trim($_POST["adresse"]));
$type          = htmlspecialchars(trim($_POST["type"]));
$id_entreprise = intval($_POST["id_entreprise"]);
 
try {
    $db = Config::getconnexion();
 
    $sql = "UPDATE offre 
            SET titre        = :titre,
                description  = :description,
                competences  = :competences,
                date_limite  = :date_limite,
                adresse      = :adresse,
                type         = :type,
                id_entreprise= :id_entreprise
            WHERE id_offer   = :id";
 
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':titre'         => $titre,
        ':description'   => $description,
        ':competences'   => $competences,
        ':date_limite'   => $date_limite,
        ':adresse'       => $adresse,
        ':type'          => $type,
        ':id_entreprise' => $id_entreprise,
        ':id'            => $id,
    ]);
 
    // Succès → retour à la liste avec message
    header("Location: listOffres.php?status=success&msg=" . urlencode("Offre mise à jour avec succès !"));
    exit;
 
} catch (Exception $e) {
    // Erreur base de données
    $msg = urlencode("Erreur base de données : " . $e->getMessage());
    header("Location: listOffres.php?status=error&msg=" . $msg);
    exit;
}
 