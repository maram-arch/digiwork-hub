<?php
require_once("../../model/Pack.php");
$packModel = new Pack();
$packs = $packModel->getAll();
?>
<!DOCTYPE html>
<html class="no-js" lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>DigiWork Hub</title>
    <meta name="description" content="Plateforme pour entrepreneurs digitaux" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="assets/css/tiny-slider.css"/>
    <link rel="stylesheet" href="assets/css/animate.css"/>
    <link rel="stylesheet" href="assets/css/lindy-uikit.css"/>
  </head>
  <body>
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left"><div class="spinner-circle"></div></div>
              <div class="spinner-right"><div class="spinner-circle"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="home" class="hero-section-wrapper-5">
      <header class="header header-6">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logo/logo.svg" alt="Logo" />
                  </a>
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent6">
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                  </button>
                  <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent6">
                    <ul id="nav6" class="navbar-nav ms-auto">
                      <li class="nav-item"><a class="page-scroll active" href="#home">Accueil</a></li>
                      <li class="nav-item"><a class="page-scroll" href="#feature">Plateforme</a></li>
                      <li class="nav-item"><a class="page-scroll" href="#pricing">Nos Packs</a></li>
                      <li class="nav-item"><a href="../back/dashboard_packs.php">Dashboard (Admin)</a></li>
                    </ul>
                  </div>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="hero-section hero-style-5 img-bg" style="background-image: url('assets/img/hero/hero-5/hero-bg.svg')">
        <div class="container">
          <div class="row">
            <div class="col-lg-6">
              <div class="hero-content-wrapper">
                <h2 class="mb-30 wow fadeInUp" data-wow-delay=".2s">Prêt à propulser vos projets ?</h2>
                <p class="mb-30 wow fadeInUp" data-wow-delay=".4s">DigiWork Hub est la plateforme ultime pour gérer vos activités, de la gestion de clients à vos abonnements prioritaires.</p>
                <a href="#pricing" class="button button-lg radius-50 wow fadeInUp page-scroll" data-wow-delay=".6s">Voir nos offres <i class="lni lni-chevron-right"></i> </a>
              </div>
            </div>
            <div class="col-lg-6 align-self-end">
              <div class="hero-image wow fadeInUp" data-wow-delay=".5s">
                <img src="assets/img/hero/hero-5/hero-img.svg" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section customized with Packs -->
    <section id="pricing" class="pricing-section pricing-style-4 bg-light pt-100 pb-100">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-5 col-lg-6">
            <div class="section-title mb-60">
              <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Abonnements et Packs</h3>
              <p class="wow fadeInUp" data-wow-delay=".4s">Choisissez le pack qui correspond parfaitement à l'échelle de vos projets et bénéficiez de tout le support nécessaire pour réussir.</p>
            </div>
          </div>
          <div class="col-xl-7 col-lg-6">
            <div class="pricing-active-wrapper wow fadeInUp" data-wow-delay=".4s">
              <div class="pricing-active">
                <?php foreach($packs as $p): ?>
                <div class="single-pricing-wrapper">
                  <div class="single-pricing">
                    <h6><?= htmlspecialchars($p['nom-pack']) ?></h6>
                    <h4>Max <?= htmlspecialchars($p['nb-proj-max']) ?> Projets</h4>
                    <h3><?= htmlspecialchars($p['prix']) ?> dt</h3>
                    <ul>
                      <li>Durée: <?= htmlspecialchars($p['duree']) ?></li>
                      <li>Support prioritaire: <?= htmlspecialchars($p['support-prioritaire']) ?></li>
                      <li><?= htmlspecialchars($p['description']) ?></li>
                    </ul>
                    <form action="../../controller/AbonnementController.php" method="POST">
                        <input type="hidden" name="pack_id" value="<?= htmlspecialchars($p['id-pack']) ?>">
                        <!-- Using ajax for frontend abonnement button if requested, but falling back to simple submit -->
                        <button type="submit" name="subscribe" class="button radius-30">S'abonner</button>
                    </form>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer footer-style-4">
      <div class="container">
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p>Design et Développé pour DigiWork Hub</p>
        </div>
      </div>
    </footer>

    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>

    <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
  </body>
</html>
