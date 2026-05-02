<?php


require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CommentaireController.php';

$controller = new CommentaireController();
$commentaires = $controller->getAllCommentaires(); 

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commentaires - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .table th, .table td { vertical-align: middle; }
        .comment-content { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2>Gestion des commentaires</h2>
                    <p class="text-muted">Total : <?= count($commentaires) ?> commentaire(s)</p>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Commentaire</th>
                                    <th>Auteur</th>
                                    <th>Date</th>
                                    <th>Publication</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($commentaires) > 0): ?>
                                    <?php foreach ($commentaires as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['id_commentaire']) ?></td>
                                        <td class="comment-content" title="<?= htmlspecialchars($c['contenu']) ?>">
                                            <?= htmlspecialchars(substr($c['contenu'], 0, 80)) ?>...
                                        </td>
                                        <td><?= htmlspecialchars(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($c['date_commentaire'])) ?></td>
                                        <td><?= htmlspecialchars($c['titre_publication'] ?? '(sans titre)') ?></td>
                                        <td>
                                            <form method="POST" action="deleteCommentaire.php" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                                <input type="hidden" name="id_commentaire" value="<?= $c['id_commentaire'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">🗑 Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun commentaire trouvé.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>