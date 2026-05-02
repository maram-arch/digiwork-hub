<?php

require_once __DIR__ . '/../../controller/CommentaireController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listCommentaires.php');
    exit;
}

$id_commentaire = (int)($_POST['id_commentaire'] ?? 0);

if ($id_commentaire) {
    $controller = new CommentaireController();
    $ok = $controller->deleteCommentaire($id_commentaire);
    
    if ($ok) {
        header("Location: listCommentaires.php?status=success&msg=" . urlencode("Commentaire supprimé avec succès."));
    } else {
        header("Location: listCommentaires.php?status=error&msg=" . urlencode("Erreur lors de la suppression."));
    }
} else {
    header("Location: listCommentaires.php?status=error&msg=" . urlencode("ID de commentaire invalide."));
}
exit;