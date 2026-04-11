<?php
require_once __DIR__ . '/../../controller/OffreController.php';
$controller = new OffreController();
$offre = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $offre = $controller->getOffre((int) $_GET['id']);
}

// Maintenant tout est généré via PHP (echo)
echo '<!DOCTYPE html>';
echo '<html lang="fr">';
echo '<head>';
echo '  <meta charset="UTF-8" />';
echo '  <meta http-equiv="X-UA-Compatible" content="IE=edge" />';
echo '  <meta name="viewport" content="width=device-width, initial-scale=1.0" />';
echo '  <title>Détail de l\'offre</title>';
echo '  <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />';
echo '  <link rel="stylesheet" href="assets/css/LineIcons.2.0.css" />';
echo '  <link rel="stylesheet" href="assets/css/tiny-slider.css" />';
echo '  <link rel="stylesheet" href="assets/css/animate.css" />';
echo '  <link rel="stylesheet" href="assets/css/lindy-uikit.css" />';
echo '</head>';
echo '<body>';
echo '  <header class="header header-6">';
echo '    <div class="navbar-area">';
echo '      <div class="container">';
echo '        <nav class="navbar navbar-expand-lg">';
echo '          <a class="navbar-brand" href="index.php"><img src="assets/img/logo/logo.png" style="width:250px;" /></a>';
echo '          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
echo '            <span class="toggler-icon"></span>';
echo '            <span class="toggler-icon"></span>';
echo '            <span class="toggler-icon"></span>';
echo '          </button>';
echo '          <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">';
echo '            <ul class="navbar-nav ms-auto">';
echo '              <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>';
echo '              <li class="nav-item"><a class="nav-link" href="offres.php">Offres</a></li>';
echo '            </ul>';
echo '          </div>';
echo '        </nav>';
echo '      </div>';
echo '    </div>';
echo '  </header>';
echo '  <main class="py-5">';
echo '    <div class="container">';

if ($offre):
    echo '      <div class="row">';
    echo '        <div class="col-lg-8">';
    echo '          <span class="badge bg-primary">' . htmlspecialchars($offre['type']) . '</span>';
    echo '          <h1 class="mt-3">' . htmlspecialchars($offre['titre']) . '</h1>';
    echo '          <p class="text-muted">Date limite : ' . htmlspecialchars($offre['date_limite']) . '</p>';
    echo '          <div class="mb-4">';
    echo '            <h5>Description</h5>';
    echo '            <p>' . nl2br(htmlspecialchars($offre['description'])) . '</p>';
    echo '          </div>';
    echo '          <div class="mb-4">';
    echo '            <h5>Compétences requises</h5>';
    echo '            <p>' . nl2br(htmlspecialchars($offre['competences'])) . '</p>';
    echo '          </div>';
    echo '          <div class="mb-4">';
    echo '            <h5>Lieu</h5>';
    echo '            <p>' . htmlspecialchars($offre['adresse']) . '</p>';
    echo '          </div>';
    echo '          <a href="offres.php" class="button radius-50">Retour à la liste</a>';
    echo '        </div>';
    echo '      </div>';
else:
    echo '      <div class="alert alert-danger">Offre introuvable ou identifiant invalide.</div>';
    echo '      <a href="offres.php" class="button radius-50">Retour à la liste</a>';
endif;

echo '    </div>';
echo '  </main>';
echo '  <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>';
echo '</body>';
echo '</html>';
?>