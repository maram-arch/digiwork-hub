<?php
// view/frontoffice/index.php - Accueil du forum

require_once __DIR__ . '/../../controller/PublicationController.php';

$controller = new PublicationController();
$result = $controller->listPublication();

// Récupération des publications
$publications = [];
if ($result instanceof PDOStatement) {
    $publications = $result->fetchAll(PDO::FETCH_ASSOC);
} elseif (is_array($result)) {
    $publications = $result;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DigiWork Hub - Forum</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/css/lindy-uikit.css" />
</head>

<body>
    <header class="header header-6">
        <div class="navbar-area">
            <div class="container">
                <nav class="navbar navbar-expand-lg">
                    <a class="navbar-brand" href="index.php"><img src="assets/img/logo/logo.png" style="width:250px;"></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                        <span class="toggler-icon"></span>
                        <span class="toggler-icon"></span>
                        <span class="toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
                            <li class="nav-item"><a class="nav-link" href="publications.php">Forum</a></li>
                            <li class="nav-item"><a class="nav-link" href="mes_commentaires.php">Mes commentaires</a></li>
                            
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-section-wrapper-5 py-5" style="background-color:#f8fafc;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="mb-4">Bienvenue sur le forum DigiWork Hub</h1>
                        <p class="mb-4">Échangez, partagez vos idées et trouvez des réponses au sein de notre communauté.</p>
                        <a class="button button-lg radius-50" href="publications.php">Voir les publications</a>
                    </div>
                    <div class="col-lg-6 text-center">
                        <img src="assets/img/hero/hero-5/hero-img.svg" alt="Illustration DigiWork Hub" class="img-fluid" />
                    </div>
                </div>
            </div>
        </section>

        <section class="pricing-section py-5">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <h2>Dernières publications</h2>
                        <p>Les sujets les plus récents du forum.</p>
                    </div>
                </div>
                <div class="row">
                    <?php if (!empty($publications)): ?>
                        <?php 
                        $recent = array_slice($publications, 0, 3); 
                        foreach ($recent as $pub): 
                            $apercu = htmlspecialchars(strip_tags($pub['contenu'] ?? ''));
                            $apercu = strlen($apercu) > 120 ? substr($apercu, 0, 120) . '…' : $apercu;
                        ?>
                            <div class="col-lg-4 mb-4">
                                <div class="pricing-card p-4 border rounded shadow-sm">
                                    <span class="badge bg-primary"><?= htmlspecialchars($pub['categorie'] ?? 'general') ?></span>
                                    <h3 class="mt-3"><?= htmlspecialchars($pub['titre'] ?? '') ?></h3>
                                    <p><?= nl2br($apercu) ?></p>
                                    <div class="text-muted small mb-2">
                                        📅 <?= date('d/m/Y', strtotime($pub['date_publication'] ?? 'now')) ?> &nbsp;|&nbsp;
                                        👤 <?= htmlspecialchars(($pub['prenom'] ?? '') . ' ' . ($pub['nom'] ?? '')) ?>
                                    </div>
                                    <a href="detail_publication.php?id=<?= urlencode($pub['id_publication']) ?>" class="button button-sm radius-50">Lire la suite</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning">Aucune publication pour le moment. Soyez le premier à poster !</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
</body>
</html>