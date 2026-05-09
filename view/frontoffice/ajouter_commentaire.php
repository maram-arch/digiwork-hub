<?php
// Ajouter un commentaire (traitement POST)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_user'])) $_SESSION['id_user'] = 1;

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Commentaire.php';
require_once __DIR__ . '/../../model/WhatsAppNotifier.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publication = (int)($_POST['id_publication'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);

    // Validation
    if ($id_publication <= 0 || strlen($contenu) < 2) {
        header('Location: detail_publication.php?id=' . $id_publication . '&status=error&msg=Contenu+trop+court');
        exit;
    }

    $contenu = htmlspecialchars($contenu);
    $parent_id = ($parent_id === 0) ? null : $parent_id;

    // Insertion du commentaire
    $success = Commentaire::add($id_publication, $_SESSION['id_user'], $contenu, $parent_id);

    if ($success) {
        // Notification WhatsApp
        $notifier = new WhatsAppNotifier();
        $notifier->notifyOwner($id_publication, $_SESSION['id_user'], 'comment');
        header('Location: detail_publication.php?id=' . $id_publication . '&status=success&msg=Commentaire+ajouté');
    } else {
        header('Location: detail_publication.php?id=' . $id_publication . '&status=error&msg=Erreur+SQL');
    }
    exit;
}
?>