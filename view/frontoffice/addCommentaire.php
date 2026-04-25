<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once '../../../controller/CommentaireController.php';
    require_once '../../../model/Commentaire.php';

    $controller = new CommentaireController();

    $commentaire = new Commentaire(
        $_POST['contenu'],
        $_POST['id_publication']
    );

    $controller->addCommentaire($commentaire);

    header("Location: detailPublication.php?id=" . $_POST['id_publication']);
    exit;
}
?>

<form method="POST">
    <textarea name="contenu" required></textarea>

    <input type="hidden" name="id_publication" value="<?= $_GET['id']; ?>">

    <button type="submit">Commenter</button>
</form>