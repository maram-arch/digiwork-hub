<?php
require_once __DIR__ . '/../../controller/OffreController.php';

$controller = new OffreController();
$offres = $controller->listOffre()->fetchAll();

// Génération de la page en PHP pur
echo '<!DOCTYPE html>';
echo '<html lang="fr">';
echo '<head>';
echo '  <meta charset="UTF-8" />';
echo '  <meta http-equiv="X-UA-Compatible" content="IE=edge" />';
echo '  <meta name="viewport" content="width=device-width, initial-scale=1.0" />';
echo '  <title>Liste des offres</title>';
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
echo '              <li class="nav-item"><a class="nav-link active" href="offres.php">Offre</a></li>';
echo '            </ul>';
echo '          </div>';
echo '        </nav>';
echo '      </div>';
echo '    </div>';
echo '  </header>';
echo '  <section class="hero-section-wrapper-5 py-5">';
echo '    <div class="container">';
echo '      <div class="row align-items-center">';
echo '        <div class="col-lg-8">';
echo '          <h1 class="mb-3">Toutes les offres disponibles</h1>';
echo '          <p class="mb-4">Découvrez les postes ouverts, les missions et les compétences recherchées.</p>';
echo '        </div>';
echo '      </div>';
echo '    </div>';
echo '  </section>';
echo '  <section class="pricing-section pb-5">';
echo '    <div class="container">';
echo '      <div class="row">';

if (!empty($offres)):
    foreach ($offres as $offre):
        echo '        <div class="col-lg-6 mb-4">';
        echo '          <div class="pricing-card p-4 border rounded shadow-sm">';
        echo '            <div class="mb-3">';
        echo '              <span class="badge bg-primary">' . htmlspecialchars($offre['type']) . '</span>';
        echo '            </div>';
        echo '            <h3>' . htmlspecialchars($offre['titre']) . '</h3>';
        echo '            <p>' . nl2br(htmlspecialchars(substr($offre['description'], 0, 180))) . '...</p>';
        echo '            <ul class="list-unstyled mb-3">';
        echo '              <li><strong>Compétences :</strong> ' . htmlspecialchars($offre['competences']) . '</li>';
        echo '              <li><strong>Adresse :</strong> ' . htmlspecialchars($offre['adresse']) . '</li>';
        echo '              <li><strong>Date limite :</strong> ' . htmlspecialchars($offre['date_limite']) . '</li>';
        echo '            </ul>';
        echo '            <a href="detail_offre.php?id=' . urlencode($offre['id_offer']) . '" class="button button-sm radius-50">Voir le détail</a>';
        echo '          </div>';
        echo '        </div>';
    endforeach;
else:
    echo '        <div class="col-12">';
    echo '          <div class="alert alert-warning">Aucune offre n\'est disponible pour le moment.</div>';
    echo '        </div>';
endif;

echo '      </div>';
echo '    </div>';
echo '  </section>';
echo '  <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>';
echo '</body>';
echo '</html>';
?>