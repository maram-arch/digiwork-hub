<?php
require_once __DIR__ . '/../../controller/PublicationController.php';

$controller = new PublicationController();
$publications = $controller->listPublication()->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DigiWork Hub - Forum</title>

  <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
  <link rel="stylesheet" href="assets/css/lindy-uikit.css">
</head>

<body>

<header class="header header-6">
  <div class="navbar-area">
    <div class="container">
      <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php">
          <img src="assets/img/logo/logo.png" style="width:250px;">
        </a>

        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link active" href="indexPublication.php">Forum</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php">Accueil</a>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
</header>

<main>

<!-- HERO -->
<section class="py-5" style="background:#f8fafc;">
  <div class="container">
    <h1>Bienvenue sur le Forum DigiWork Hub</h1>
    <p>Échangez, publiez et discutez avec la communauté.</p>

    <a href="listPublication.php" class="button button-lg radius-50">
      Voir toutes les publications
    </a>
  </div>
</section>

<!-- RECENT POSTS -->
<section class="py-5">
  <div class="container">

    <h2>Publications récentes</h2>
    <p>Les derniers posts de la communauté</p>

    <div class="row mt-4">

      <?php if (!empty($publications)): ?>
        <?php foreach (array_slice($publications, 0, 3) as $pub): ?>
          <div class="col-lg-4 mb-4">

            <div class="border rounded p-3 shadow-sm">

              <span class="badge bg-primary">
                <?= htmlspecialchars($pub['categorie']) ?>
              </span>

              <h4 class="mt-2">
                <?= htmlspecialchars($pub['titre']) ?>
              </h4>

              <p>
                <?= nl2br(htmlspecialchars(substr($pub['contenu'], 0, 100))) ?>...
              </p>

              <a href="index.php?action=voir&id=<?= $pub['id_publication'] ?>"
                 class="button button-sm radius-50">
                Lire
              </a>

            </div>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning">
            Aucune publication disponible
          </div>
        </div>
      <?php endif; ?>

    </div>

  </div>
</section>

</main>

<script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
</body>
</html>