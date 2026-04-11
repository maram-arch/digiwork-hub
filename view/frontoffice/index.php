<?php
require_once __DIR__ . '/../../controller/OffreController.php';
$controller = new OffreController();
$offres = $controller->listOffre()->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DigiWork Hub - Accueil</title>
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
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="toggler-icon"></span>
            <span class="toggler-icon"></span>
            <span class="toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
              <li class="nav-item"><a class="nav-link" href="offres.php">Offre</a></li>
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
            <h1 class="mb-4">Bienvenue sur DigiWork Hub</h1>
            <p class="mb-4">Explorez notre espace de recrutement et trouvez l’offre qui correspond à vos compétences.</p>
            <a class="button button-lg radius-50" href="offres.php">Voir les offres</a>
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
            <h2>Offres r�centes</h2>
            <p>Les derni�res opportunit�s publi�es par nos partenaires.</p>
          </div>
        </div>
        <div class="row">
          <?php if (!empty($offres)): ?>
            <?php foreach (array_slice($offres, 0, 3) as $offre): ?>
              <div class="col-lg-4 mb-4">
                <div class="pricing-card p-4 border rounded shadow-sm">
                  <span class="badge bg-primary"><?php echo htmlspecialchars($offre['type']); ?></span>
                  <h3 class="mt-3"><?php echo htmlspecialchars($offre['titre']); ?></h3>
                  <p><?php echo nl2br(htmlspecialchars(substr($offre['description'], 0, 120))); ?>...</p>
                  <a href="detail_offre.php?id=<?php echo urlencode($offre['id_offer']); ?>" class="button button-sm radius-50">Voir le d�tail</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-warning">Aucune offre disponible pour le moment.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
</body>
</html>
