<?php
// view/backoffice/listPublications.php
// Liste des publications (forum)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/PublicationController.php';

$controller = new PublicationController();
$result = $controller->listPublication();
$publications = [];

if ($result instanceof PDOStatement) {
    $publications = $result->fetchAll(PDO::FETCH_ASSOC);
} elseif (is_array($result)) {
    $publications = $result;
}

$message = $messageType = "";
if (isset($_GET['status'], $_GET['msg'])) {
    $messageType = ($_GET['status'] === 'success') ? 'success' : 'danger';
    $message = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des publications - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .badge-cat { background: #e9ecef; padding: 4px 8px; border-radius: 20px; font-size: 11px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>Liste des publications</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>
    
    <a href="addPublication.php" class="btn btn-primary mb-3">Ajouter une publication</a>
    
    <table class="table table-bordered">
        <thead>
            <tr><th>ID</th><th>Titre</th><th>Catégorie</th><th>Auteur</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (count($publications) > 0): ?>
            <?php foreach ($publications as $p): ?>
            <tr>
                <td><?= $p['id_publication'] ?? $p['id-publication'] ?? '?' ?></td>
                <td><?= htmlspecialchars($p['titre'] ?? '') ?></td>
                <td><span class="badge-cat"><?= $p['categorie'] ?? 'general' ?></span></td>
                <td><?= htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '')) ?></td>
                <td><?= date('d/m/Y', strtotime($p['date_publication'] ?? 'now')) ?></td>
                <td>
                    <a href="updatePublication.php?id=<?= $p['id_publication'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="deletePublication.php?id=<?= $p['id_publication'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">Aucune publication</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>