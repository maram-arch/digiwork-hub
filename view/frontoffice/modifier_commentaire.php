<style id="theme-style">
    body { background: #fff; color: #000; transition: 0.3s; }
    body.dark-mode { background: #1e1e2f; color: #eee; }
    body.dark-mode .card, body.dark-mode .pub-card, body.dark-mode .form-container { background: #2a2a3a; color: #eee; border-color: #444; }
</style>
<button id="theme-toggle" class="btn btn-sm btn-secondary position-fixed bottom-0 end-0 m-3">🌓 Thème</button>
<script>
document.getElementById('theme-toggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
</script>
<?php

session_start();
require_once __DIR__ . '/../../controller/CommentaireController.php';

if (!isset($_SESSION['id_user'])) $_SESSION['id_user'] = 1;
$id_user = $_SESSION['id_user'];

// Récupère l'ID soit en GET (affichage initial) soit en POST (soumission)
$id_commentaire = (int)($_GET['id'] ?? $_POST['id_commentaire'] ?? 0);
if (!$id_commentaire) {
    header('Location: mes_commentaires.php?status=error&msg=Commentaire introuvable');
    exit;
}

$controller = new CommentaireController();
$commentaire = $controller->getCommentaireById($id_commentaire);

if (!$commentaire || $commentaire['id_user'] != $id_user) {
    header('Location: mes_commentaires.php?status=error&msg=Action non autorisée');
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_contenu = trim($_POST['contenu'] ?? '');
    if (strlen($nouveau_contenu) < 2) {
        $message = "Le commentaire doit contenir au moins 2 caractères.";
    } elseif (preg_match('/<[^>]*>/', $nouveau_contenu)) {
        $message = "Les balises HTML ne sont pas autorisées.";
    } else {
        $ok = $controller->updateCommentaire($id_commentaire, htmlspecialchars($nouveau_contenu));
        if ($ok) {
            header('Location: mes_commentaires.php?status=success&msg=Commentaire modifié');
            exit;
        } else {
            $message = "Erreur lors de la mise à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon commentaire</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <style>
        body { background: #f0f2f9; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 700px; margin: 40px auto; }
        .card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .btn-primary { background: #435ebe; border: none; }
        .btn-primary:hover { background: #3348a8; }
    </style>
</head>

<body>
<div class="container">
    <div class="card">
        <h2>Modifier votre commentaire</h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="id_commentaire" value="<?= $id_commentaire ?>">
            <div class="mb-3">
                <label for="contenu" class="form-label">Commentaire</label>
                <textarea name="contenu" id="contenu" class="form-control" rows="5" required minlength="2"><?= htmlspecialchars($commentaire['contenu']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="mes_commentaires.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
</body>
</html>