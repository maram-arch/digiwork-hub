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

$id_commentaire = (int)($_GET['id_commentaire'] ?? 0);
if (!$id_commentaire) {
    header('Location: mes_commentaires.php?status=error&msg=ID+manquant');
    exit;
}

$controller = new CommentaireController();
$commentaire = $controller->getCommentaireById($id_commentaire);
if (!$commentaire || $commentaire['id_user'] != $id_user) {
    header('Location: mes_commentaires.php?status=error&msg=Non+autorisé');
    exit;
}

$success = $controller->deleteCommentaire($id_commentaire);
$msg = $success ? 'Commentaire supprimé' : 'Erreur suppression';
$status = $success ? 'success' : 'error';
header("Location: mes_commentaires.php?status=$status&msg=" . urlencode($msg));
exit;