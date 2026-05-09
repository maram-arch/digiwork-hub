<?php
session_start();
require_once __DIR__ . '/../../model/Publication.php';
require_once __DIR__ . '/../../config/config.php';

$id_users = $_SESSION['id_users'] ?? 1;
$pdo = Config::getConnexion();
$stmt = $pdo->prepare("
    SELECT f.*, u.nom, u.prenom
    FROM forums f
    INNER JOIN favoris fav ON f.id_publication = fav.id_publication
    LEFT JOIN user u ON f.id_user = u.id_user
    WHERE fav.id_user = ?
");
$stmt->execute([$id_users]);
$favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes favoris - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <style>
        body { background: #f0f2f9; }
        body.dark-mode { background: #1e1e2f; color: #eee; }
        .card { border-radius: 16px; margin-bottom: 20px; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: #435ebe; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer; }
    </style>
</head>

<body>
<header class="header header-6">...</header>
<div class="container mt-4">
    <h2>⭐ Mes publications favorites</h2>
    <div class="row">
        <?php if (empty($favoris)): ?>
            <div class="col-12"><div class="alert alert-info">Aucune publication favorite.</div></div>
        <?php else: ?>
            <?php foreach ($favoris as $pub): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($pub['titre']) ?></h5>
                            <p class="card-text small"><?= htmlspecialchars(substr($pub['contenu'], 0, 100)) ?>...</p>
                            <a href="detail_publication.php?id=<?= $pub['id_publication'] ?>" class="btn btn-sm btn-primary">Voir</a>
                            <button class="btn btn-sm btn-danger remove-fav" data-id="<?= $pub['id_publication'] ?>">Retirer</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<button id="themeToggle" class="theme-toggle">🌓</button>
<script>
document.querySelectorAll('.remove-fav').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        fetch('publications.php?action=favori', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=' + id
        }).then(() => location.reload());
    });
});
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
</script>
</body>
</html>