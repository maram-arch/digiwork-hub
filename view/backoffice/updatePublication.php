<?php
// backoffice/updatePublication.php
// Mise à jour d'une publication (forum)

require_once __DIR__ . '/../../controller/PublicationController.php';
require_once __DIR__ . '/../../model/Publication.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listPublications.php");
    exit;
}

// ── Validation des champs obligatoires ─────────────────────────
$errors = [];
if (empty($_POST["id_publication"])) $errors[] = "ID publication manquant.";
if (empty($_POST["titre"]))          $errors[] = "Le titre est obligatoire.";
if (empty($_POST["contenu"]))        $errors[] = "Le contenu est obligatoire.";
if (empty($_POST["categorie"]))      $errors[] = "La catégorie est obligatoire.";

// Validation supplémentaire longueur (côté serveur)
if (!empty($_POST["titre"]) && strlen(trim($_POST["titre"])) < 3) {
    $errors[] = "Le titre doit contenir au moins 3 caractères.";
}
if (!empty($_POST["contenu"]) && strlen(trim($_POST["contenu"])) < 10) {
    $errors[] = "Le contenu doit contenir au moins 10 caractères.";
}

if (empty($errors)) {
    // Validation des champs événement si la case est cochée
    $is_event = isset($_POST["is_event"]) ? 1 : 0;
    if ($is_event == 1) {
        if (empty($_POST["event_date"])) {
            $errors[] = "La date de l'événement est obligatoire.";
        } else {
            $event_date = trim($_POST["event_date"]);
            $dateObj = DateTime::createFromFormat('Y-m-d', $event_date);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $event_date) {
                $errors[] = "La date de l'événement doit être au format YYYY-MM-DD.";
            }
        }
        if (empty($_POST["event_lieu"])) {
            $errors[] = "Le lieu de l'événement est obligatoire.";
        }
    }
}

if (!empty($errors)) {
    header("Location: listPublications.php?status=error&msg=" . urlencode(implode(" | ", $errors)));
    exit;
}

// ── Récupération de l'ancienne image (pour la supprimer si nouvelle) ──
$id_publication = intval($_POST["id_publication"]);
$oldImage = null;

// On a besoin de l'ancienne image depuis la base (optionnel mais recommandé)
try {
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT image FROM forums WHERE id_publication = :id");
    $stmt->execute(['id' => $id_publication]);
    $oldImage = $stmt->fetchColumn();
} catch (Exception $e) {
    // Si erreur, on continue sans supprimer l'ancienne (log)
}

// ── Gestion de l'upload d'une nouvelle image ─────────────────────
$imageName = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../public/uploads/publications/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('pub_') . '.' . $extension;
    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imageName = 'uploads/publications/' . $fileName;
        // Supprimer l'ancienne image si elle existe
        if ($oldImage && file_exists(__DIR__ . '/../../' . $oldImage)) {
            unlink(__DIR__ . '/../../' . $oldImage);
        }
    } else {
        header("Location: listPublications.php?status=error&msg=" . urlencode("Erreur lors de l'upload de l'image."));
        exit;
    }
} else {
    // Conserver l'ancienne image si aucune nouvelle n'est fournie
    $imageName = $oldImage;
}

// ── Nettoyage des entrées ───────────────────────────────────────
$titre      = htmlspecialchars(trim($_POST["titre"]));
$contenu    = htmlspecialchars(trim($_POST["contenu"]));
$categorie  = htmlspecialchars(trim($_POST["categorie"]));
$is_event   = isset($_POST["is_event"]) ? 1 : 0;
$event_date = ($is_event && !empty($_POST["event_date"])) ? $_POST["event_date"] : null;
$event_lieu = ($is_event && !empty($_POST["event_lieu"])) ? htmlspecialchars(trim($_POST["event_lieu"])) : null;

// ── Instanciation du modèle Publication ─────────────────────────
$publication = new Publication(
    $id_publication,
    $titre,
    $contenu,
    null, // date_publication inchangée
    null, // id_user inchangé
    $categorie,
    $imageName,
    null, // statut inchangé
    null, // nb_vues
    null, // nb_likes
    $is_event,
    $event_date,
    $event_lieu
);

// ── Appel du contrôleur ────────────────────────────────────────
try {
    $controller = new PublicationController();
    $controller->updatePublication($publication, $id_publication);

    header("Location: listPublications.php?status=success&msg=" . urlencode("Publication mise à jour avec succès !"));
    exit;
} catch (Exception $e) {
    header("Location: listPublications.php?status=error&msg=" . urlencode("Erreur base de données : " . $e->getMessage()));
    exit;
}
?>