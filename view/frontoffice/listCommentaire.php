<?php
require_once '../../../controller/CommentaireController.php';

$controller = new CommentaireController();

$commentaires = $controller->listCommentaires($_GET['id']);
?>

<h3>Commentaires</h3>

<?php foreach ($commentaires as $c): ?>
    <div class="comment">

        <p><?= htmlspecialchars($c['contenu']); ?></p>

        <small><?= $c['date_creation']; ?></small>

        <a href="deleteCommentaire.php?id=<?= $c['id_commentaire']; ?>">🗑</a>
        <a href="editCommentaire.php?id=<?= $c['id_commentaire']; ?>">✏️</a>

    </div>
<?php endforeach; ?>