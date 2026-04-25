<?php
require_once '../../../controller/CommentaireController.php';

$controller = new CommentaireController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $controller->updateCommentaire($_POST['id'], $_POST['contenu']);

    header("Location: detailPublication.php?id=" . $_GET['pub']);
    exit;
}
?>

<form method="POST">
    <textarea name="contenu" required></textarea>

    <input type="hidden" name="id" value="<?= $_GET['id']; ?>">

    <button type="submit">Modifier</button>
</form>