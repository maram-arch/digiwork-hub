<?php
session_start();
if (!isset($_SESSION['id_user'])) $_SESSION['id_user'] = 1;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Commentaire.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publication = (int)($_POST['id_publication'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    if ($id_publication <= 0 || strlen($contenu) < 2) {
        header('Location: detail_publication.php?id=' . $id_publication . '&status=error&msg=Contenu+trop+court');
        exit;
    }
    $contenu = htmlspecialchars($contenu);
    $parent_id = ($parent_id === 0) ? null : $parent_id;
    $com = new Commentaire(0, $contenu, $id_publication, $_SESSION['id_user'], $parent_id);
    if ($com->addCommentaire()) {
        header('Location: detail_publication.php?id=' . $id_publication . '&status=success&msg=Commentaire+ajouté');
    } else {
        header('Location: detail_publication.php?id=' . $id_publication . '&status=error&msg=Erreur+SQL');
    }
    exit;
}
?>