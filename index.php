
<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controller/UserController.php';

$action = $_GET['action'] ?? '';
$page   = $_GET['page']   ?? 'home';

// ─── Auth API handlers ────────────────────────────────────────────────────────

if ($action === 'logout') {
    if (isset($_SESSION['user_id'])) {
        try { (new UserController())->logoutUser((int) $_SESSION['user_id']); } catch (Throwable $e) {}
    }
    header('Location: index.php');
    exit;
}

if ($action === 'heartbeat' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false]); exit; }
    try {
        (new UserController())->touchUserSession((int) $_SESSION['user_id']);
        echo json_encode(['success' => true]);
    } catch (Throwable $e) { echo json_encode(['success' => false]); }
    exit;
}

if (in_array($action, ['login','signup','verify_otp','resend_otp','forgot_password','verify_reset_otp','resend_reset_otp','reset_password'], true)
    && $_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');
    try {
        $controller = new UserController();
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Service temporairement indisponible. Vérifiez que MySQL est démarré dans XAMPP.']);
        exit;
    }

    $payload = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($payload)) { $payload = $_POST; }

    if ($action === 'login') {
        $result = $controller->login($payload);
        if ($result['success']) {
            $newUserId = (int) $result['user']['id'];
            $oldUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            if ($oldUserId > 0 && $oldUserId !== $newUserId) {
                try { $controller->markUserOffline($oldUserId); } catch (Throwable $e) {}
            }
            session_regenerate_id(true);
            $_SESSION['user_id']    = $newUserId;
            $_SESSION['front_auth'] = true;
            $controller->touchUserSession($newUserId);
            $result['redirect'] = 'view/backoffice/index.php';
        }
        echo json_encode($result);
    } elseif ($action === 'verify_otp') {
        $result = $controller->verifyOtp($payload);
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int) $result['user_id'];
            $_SESSION['front_auth'] = true;
            $controller->touchUserSession((int) $result['user_id']);
            $result['redirect'] = 'view/backoffice/index.php';
        }
        echo json_encode($result);
    } elseif ($action === 'resend_otp') {
        echo json_encode($controller->resendOtp($payload));
    } elseif ($action === 'forgot_password') {
        echo json_encode($controller->forgotPassword($payload));
    } elseif ($action === 'verify_reset_otp') {
        echo json_encode($controller->verifyResetOtp($payload));
    } elseif ($action === 'resend_reset_otp') {
        echo json_encode($controller->resendResetOtp($payload));
    } elseif ($action === 'reset_password') {
        echo json_encode($controller->resetPassword($payload));
    } else {
        // signup
        $result = $controller->signup($payload);
        if (isset($result['errors'])) {
            echo json_encode(['success' => false, 'message' => implode(' ', $result['errors'])]);
        } else {
            echo json_encode($result);
        }
    }
    exit;
}

// ─── Page router for non-home pages ──────────────────────────────────────────
// For sub-pages: capture module output, strip its own head/navbar/body tags,
// then render the content inside the landing page layout (same navbar).

$nonHomePages = ['events','inscription','projets','explore','packs','abonnement'];
if (in_array($page, $nonHomePages, true)) {

    // Capture the module's full HTML output
    ob_start();
    switch ($page) {
        case 'events':
            require __DIR__ . '/view/frontoffice/modules/event.php';
            break;
        case 'inscription':
            require __DIR__ . '/view/frontoffice/modules/inscription.php';
            break;
        case 'projets':
            require __DIR__ . '/view/frontoffice/modules/projets.php';
            break;
        case 'explore':
            require __DIR__ . '/view/frontoffice/modules/exploreProjects.php';
            break;
        case 'packs':
            require __DIR__ . '/view/frontoffice/modules/packs.php';
            break;
        case 'abonnement':
            require __DIR__ . '/view/frontoffice/modules/abonnement.php';
            break;
    }
    $moduleOutput = ob_get_clean();

    // Extract only the <style> blocks from the module's <head> (keep module CSS)
    $moduleStyles = '';
    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/si', $moduleOutput, $styleMatches)) {
        foreach ($styleMatches[1] as $css) {
            $moduleStyles .= '<style>' . $css . '</style>' . "\n";
        }
    }

    // Extract content after the last </nav> or </header> (strip the module's own navbar)
    // Try </nav> first, then </header>, then <body>
    $bodyContent = '';
    foreach (['</nav>', '</header>'] as $marker) {
        $pos = strrpos($moduleOutput, $marker);
        if ($pos !== false) {
            $bodyContent = substr($moduleOutput, $pos + strlen($marker));
            break;
        }
    }
    if ($bodyContent === '') {
        // Fallback: extract between <body> and </body>
        if (preg_match('/<body[^>]*>(.*)<\/body>/si', $moduleOutput, $m)) {
            $bodyContent = $m[1];
        } else {
            $bodyContent = $moduleOutput;
        }
    }

    // Strip trailing </body></html>
    $bodyContent = preg_replace('/<\/body>\s*<\/html>\s*$/si', '', $bodyContent);

    // Also extract any <script> tags that are NOT inside <head> (module JS at bottom)
    // They are already included in $bodyContent since they come after </nav>

    // Store for rendering in the layout below
    $pageContent   = $bodyContent;
    $pageStyles    = $moduleStyles;
    $renderSubPage = true;
} else {
    $renderSubPage = false;
}

// ─── Auth state for home page ─────────────────────────────────────────────────

if (isset($_SESSION['user_id'])) {
    try {
        $currentUser = (new UserController())->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) { $currentUser = null; }
} else {
    $currentUser = null;
}

$frontAuthState = [
    'loggedIn'  => $currentUser !== null,
    'frontAuth' => !empty($_SESSION['front_auth']),
    'userId'    => (int) ($currentUser['id_user'] ?? 0),
    'role'      => (string) ($currentUser['role'] ?? ''),
    'email'     => (string) ($currentUser['email'] ?? ''),
];

// ─── Load packs from DB ───────────────────────────────────────────────────────
require_once __DIR__ . '/model/Pack.php';
$packModel = new Pack();
try { $packs = $packModel->getAll(); } catch (Throwable $e) { $packs = []; }
?>
<!DOCTYPE html>
<html class="no-js" lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>DigiWork Hub</title>
    <meta name="description" content="Plateforme pour entrepreneurs digitaux" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="assets/css/tiny-slider.css"/>
    <link rel="stylesheet" href="assets/css/animate.css"/>
    <link rel="stylesheet" href="assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="view/frontoffice/frontoffice-auth-modals.css" />
    <style>
      /* ── Unified palette: blue #1b4379 / #2270c1 + green #69b83b ── */

      /* Navbar */
      .navbar-area { background: transparent !important; box-shadow: none !important; }
      .navbar { padding: 10px 0; }
      .navbar-brand img { height: 90px; width: auto; object-fit: contain; }
      /* Force main layout nav to never be white — overrides any module CSS */
      #mainNavbarArea .navbar,
      #mainNavbarArea nav.navbar {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
        position: static !important;
      }
      body.subpage-mode #mainNavbarArea .navbar,
      body.subpage-mode #mainNavbarArea nav.navbar {
        background: #1b4379 !important;
        position: static !important;
      }

      /* Nav links */
      .navbar-nav .nav-item a {
        color: #1b4379 !important;
        font-weight: 600;
        font-size: 15px;
        padding: 8px 14px;
        text-decoration: none;
        transition: color .2s;
      }
      .navbar-nav .nav-item a:hover { color: #2270c1 !important; }

      /* Header action buttons */
      .header-action a { text-decoration: none; }
      .header-action .btn-login {
        font-size: 14px; font-weight: 600; color: #1b4379;
        padding: 7px 16px; border: 2px solid #1b4379; border-radius: 6px;
        transition: background .2s, color .2s;
      }
      .header-action .btn-login:hover { background: #1b4379; color: #fff; }
      .header-action .btn-signup {
        font-size: 14px; font-weight: 600; color: #fff;
        padding: 7px 18px; background: #69b83b; border-radius: 6px;
        border: 2px solid #69b83b; transition: background .2s;
      }
      .header-action .btn-signup:hover { background: #57a02e; border-color: #57a02e; }
      .header-action .btn-dashboard {
        font-size: 13px; font-weight: 600; color: #1b4379;
      }
      .header-action .btn-logout {
        font-size: 13px; font-weight: 600; color: #e53e3e;
      }

      /* ── Hamburger button ── */
      #hamburgerBtn {
        background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.6);
        cursor: pointer; font-size: 22px; color: #fff; padding: 6px 12px;
        line-height: 1; display: none; border-radius: 6px;
        backdrop-filter: blur(4px);
      }
      #hamburgerBtn:hover { background: rgba(255,255,255,0.25); }

      /* ── Dropdown menu ── */
      #navDropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        min-width: 220px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(27,67,121,.18);
        z-index: 9999;
        overflow: hidden;
        border: 1px solid #e3eaf5;
      }
      #navDropdown.open { display: block; }
      #navDropdown a {
        display: block;
        padding: 11px 20px;
        color: #1b4379;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: background .15s;
      }
      #navDropdown a:hover { background: #eef4ff; color: #2270c1; }
      #navDropdown .nav-divider {
        height: 1px; background: #e3eaf5; margin: 4px 0;
      }
      #navDropdown .nav-auth-link { color: #555; font-weight: 500; }
      #navDropdown .nav-logout-link { color: #e53e3e; }
      #navDropdown .nav-signup-link { color: #69b83b; }

      /* Hamburger wrapper (relative for dropdown positioning) */
      .hamburger-wrapper { position: relative; }

      /* Hide Bootstrap collapse on all screens — we use our own dropdown */
      .sub-menu-bar { display: none !important; }

      /* Show hamburger always */
      #hamburgerBtn { display: block; }

      /* ── Hero gradient ── */
      .hero-section.hero-style-5 {
        background: linear-gradient(135deg, #1b4379 0%, #2270c1 100%) !important;
      }
      .hero-section.hero-style-5 h2 { color: #fff; }
      .hero-section.hero-style-5 h4 { color: #69b83b !important; }
      .hero-section.hero-style-5 p { color: rgba(255,255,255,.88); }

      /* Hide hero background on sub-pages */
      .subpage-mode .hero-section-wrapper-5 .hero-section {
        background: transparent !important;
        padding: 0 !important;
        min-height: 0 !important;
        display: none !important;
      }
      .subpage-mode .hero-section-wrapper-5 {
        background: transparent !important;
      }

      /* Primary buttons → blue */
      .button { background: #1b4379 !important; border-color: #1b4379 !important; color: #fff !important; }
      .button:hover { background: #2270c1 !important; border-color: #2270c1 !important; }

      /* CTA / events button → green */
      .button.btn-cta,
      a[href*="events"].button,
      a[data-wow-delay=".8s"].button {
        background: #69b83b !important;
        border-color: #69b83b !important;
      }
      a[data-wow-delay=".8s"].button:hover {
        background: #57a02e !important; border-color: #57a02e !important;
      }

      /* Pricing cards */
      .single-pricing { border-top: 4px solid #1b4379; }
      .single-pricing h6 { color: #1b4379; }
      .single-pricing h3 { color: #2270c1; }
      .single-pricing .button { background: #69b83b !important; border-color: #69b83b !important; }
      .single-pricing .button:hover { background: #57a02e !important; border-color: #57a02e !important; }

      /* Admin/dashboard link in header */
      .header-action .btn-dashboard { color: #1b4379; }

      /* ── Sub-page navbar: switch from absolute to relative so it sits above content ── */
      body.subpage-mode #mainNavbarArea {
        position: relative !important;
        background: #1b4379 !important;
        box-shadow: 0 2px 12px rgba(27,67,121,.18) !important;
      }
      body.subpage-mode .hero-section-wrapper-5 {
        padding-top: 0 !important;
      }
      /* Navbar top padding for home page (absolute positioned over hero) */
      body:not(.subpage-mode) .hero-section.hero-style-5 {
        padding-top: 100px;
      }

      /* ── Kill the white navbar band injected by sub-page modules ── */
      /* The modules have their own <header class="navbar"> / <nav class="navbar">
         which gets stripped from HTML but their CSS still loads.
         Override everything to hidden/zero so no white band appears. */
      body.subpage-mode #subpage-content > header,
      body.subpage-mode #subpage-content > nav,
      body.subpage-mode #subpage-content header.navbar,
      body.subpage-mode #subpage-content nav.navbar {
        display: none !important;
      }
      /* Remove top padding/margin from first element inside subpage */
      body.subpage-mode #subpage-content > *:first-child {
        margin-top: 0 !important;
      }    </style>
  </head>
  <body<?= $renderSubPage ? ' class="subpage-mode"' : '' ?>>
    <!-- Auth state injected for JS -->
    <script>window.__FRONT_AUTH_STATE__ = <?= json_encode($frontAuthState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>

    <!-- ========================= Login Modal (always present) ========================= -->
    <div id="loginModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3>Connexion</h3>
          <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
        </div>
        <div class="modal-body">
          <div id="loginAlert" class="alert-message"></div>
          <form id="loginForm">
            <div class="form-group">
              <label>Email</label>
              <input type="text" id="loginEmail" placeholder="exemple@domaine.com">
            </div>
            <div class="form-group">
              <label>Mot de passe</label>
              <input type="password" id="loginPassword" placeholder="Mot de passe">
              <div style="text-align:right;margin-top:4px;">
                <a href="#" id="forgotPasswordLink" style="font-size:13px;color:#667eea;text-decoration:none;">Mot de passe oublié ?</a>
              </div>
            </div>
            <button type="submit" class="btn-submit">Se connecter</button>
          </form>
          <div class="switch-form">
            Pas encore de compte ?
            <a class="switch-link" href="#" onclick="event.preventDefault(); switchToSignup();">S'inscrire</a>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================= Signup Modal ========================= -->
    <div id="signupModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3>Inscription</h3>
          <button class="modal-close" onclick="closeModal('signupModal')">&times;</button>
        </div>
        <div class="modal-body">
          <div id="signupAlert" class="alert-message"></div>
          <form id="signupForm">
            <div class="form-group">
              <label>Email</label>
              <input type="text" id="signupEmail" placeholder="exemple@domaine.com">
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input type="text" id="signupTel" placeholder="8 chiffres">
            </div>
            <div class="form-group">
              <label>Mot de passe</label>
              <input type="password" id="signupPassword" autocomplete="new-password"
                     placeholder="10+ caractères, maj/min, chiffre, symbole" aria-describedby="signupPasswordStrength">
              <div id="signupPasswordStrength" class="password-strength" aria-live="polite" style="display:none;"></div>
            </div>
            <div class="form-group">
              <label>Confirmer mot de passe</label>
              <input type="password" id="signupConfirmPassword" placeholder="Confirmer le mot de passe">
            </div>
            <div class="form-group">
              <label>Je suis :</label>
              <div class="role-selector">
                <div class="role-option" data-role="candidat" onclick="selectRole('candidat')"><i class="lni lni-user"></i> Candidat</div>
                <div class="role-option" data-role="entreprise" onclick="selectRole('entreprise')"><i class="lni lni-briefcase"></i> Entreprise</div>
                <div class="role-option" data-role="sponsor" onclick="selectRole('sponsor')"><i class="lni lni-gift"></i> Sponsor</div>
                <div class="role-option" data-role="admin" onclick="selectRole('admin')"><i class="lni lni-shield"></i> Admin</div>
              </div>
            </div>
            <input type="hidden" id="signupRole" value="candidat">
            <!-- Champs Candidat -->
            <div id="candidatFields" class="dynamic-fields">
              <div class="form-group"><label>Nom</label><input type="text" id="candidatNom"></div>
              <div class="form-group"><label>Prénom</label><input type="text" id="candidatPrenom"></div>
              <div class="form-group"><label>Date de naissance</label><input type="text" id="candidatDdn" placeholder="AAAA-MM-JJ"></div>
            </div>
            <!-- Champs Entreprise -->
            <div id="entrepriseFields" class="dynamic-fields">
              <div class="form-group"><label>Nom de l'entreprise</label><input type="text" id="entrepriseNom"></div>
              <div class="form-group"><label>Adresse</label><input type="text" id="entrepriseAdresse"></div>
              <div class="form-group"><label>Description</label><textarea id="entrepriseDescription" rows="3"></textarea></div>
            </div>
            <!-- Champs Sponsor -->
            <div id="sponsorFields" class="dynamic-fields">
              <div class="form-group"><label>Nom du sponsor</label><input type="text" id="sponsorNom"></div>
              <div class="form-group"><label>Prénom</label><input type="text" id="sponsorPrenom"></div>
              <div class="form-group"><label>Société</label><input type="text" id="sponsorSociete"></div>
            </div>
            <!-- Champs Admin -->
            <div id="adminFields" class="dynamic-fields">
              <div class="form-group"><label>Nom</label><input type="text" id="adminNom"></div>
              <div class="form-group"><label>Prénom</label><input type="text" id="adminPrenom"></div>
              <div class="form-group"><label>Code Admin</label><input type="text" id="adminCode" placeholder="Code d'administration"></div>
              <div class="form-group"><label>Date de naissance</label><input type="text" id="adminDdn" placeholder="AAAA-MM-JJ"></div>
            </div>
            <button type="submit" class="btn-submit">S'inscrire</button>
          </form>
          <div class="switch-form">
            Déjà un compte ?
            <a class="switch-link" href="#" onclick="event.preventDefault(); switchToLogin();">Se connecter</a>
          </div>
        </div>
      </div>
    </div>
    <!-- ========================= End Modals ========================= -->

    <!-- ========================= preloader start ========================= -->
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
    <!-- ========================= preloader end ========================= -->

    <!-- ========================= Navbar ========================= -->
    <section id="home" class="hero-section-wrapper-5">
      <header class="header header-6">
        <div class="navbar-area" style="background: transparent; box-shadow: none; position: absolute; top: 0; left: 0; right: 0; z-index: 100;"
             id="mainNavbarArea">
          <div class="container-fluid px-4">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:nowrap;padding:12px 0;">
                  <!-- Logo — top left, bigger -->
                  <a class="navbar-brand" href="index.php" style="margin:0;padding:0;">
                    <img src="assets/img/logo/logo.png" alt="DigiWork Hub" style="height:90px;width:auto;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3));" />
                  </a>

                  <!-- Hamburger + dropdown wrapper -->
                  <div class="hamburger-wrapper">
                    <button id="hamburgerBtn" aria-label="Menu" aria-expanded="false" aria-controls="navDropdown">
                      &#9776;
                    </button>

                    <!-- Vertical dropdown -->
                    <div id="navDropdown" role="menu">
                      <a href="index.php" role="menuitem">Accueil</a>
                      <a href="index.php?page=events" role="menuitem">Événements</a>
                      <a href="index.php?page=packs" role="menuitem">Packs</a>
                      <a href="index.php?page=projets" role="menuitem">Projets</a>
                      <a href="#contact" class="page-scroll" role="menuitem">Contact</a>
                      <div class="nav-divider"></div>
                      <?php if ($frontAuthState['loggedIn']): ?>
                        <a class="nav-auth-link" style="cursor:default;font-size:12px;color:#888;padding-bottom:4px;" role="menuitem" tabindex="-1">
                          <?= htmlspecialchars($frontAuthState['email'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <a href="view/backoffice/index.php" class="nav-auth-link" role="menuitem">
                          &#9783; Dashboard
                        </a>
                        <a href="index.php?action=logout" class="nav-logout-link" role="menuitem">Déconnexion</a>
                      <?php else: ?>
                        <a href="#" class="nav-auth-link" onclick="event.preventDefault(); document.getElementById('navDropdown').classList.remove('open'); openModal('loginModal');" role="menuitem">
                          Connexion
                        </a>
                        <a href="#" class="nav-signup-link" onclick="event.preventDefault(); document.getElementById('navDropdown').classList.remove('open'); openModal('signupModal');" role="menuitem">
                          Inscription
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </header>

      <script>
      (function() {
        var btn = document.getElementById('hamburgerBtn');
        var menu = document.getElementById('navDropdown');
        if (!btn || !menu) return;

        btn.addEventListener('click', function(e) {
          e.stopPropagation();
          var isOpen = menu.classList.toggle('open');
          btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function(e) {
          if (!menu.contains(e.target) && e.target !== btn) {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
          }
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
          }
        });
      })();
      </script>
      <div class="hero-section hero-style-5 img-bg" style="background-image: url('assets/img/hero/hero-5/hero-bg.svg')">
        <?php if (!$renderSubPage): ?>
        <div class="container">
          <div class="row">
            <div class="col-lg-6">
              <div class="hero-content-wrapper">
                <h2 class="mb-30 wow fadeInUp" data-wow-delay=".2s">DigiWork Hub</h2>
                <h4 class="mb-20 wow fadeInUp" data-wow-delay=".3s" style="color:#69b83b;">Plateforme intelligente d'accompagnement des entrepreneurs digitaux</h4>
                <p class="mb-30 wow fadeInUp" data-wow-delay=".4s">Structuration d'activité, optimisation des revenus, matching IA et développement durable. Rejoignez la nouvelle génération de plateformes digitales.</p>
                <a href="#pricing" class="button button-lg radius-50 wow fadeInUp page-scroll" data-wow-delay=".6s">Voir nos offres <i class="lni lni-chevron-right"></i></a>
                <a href="index.php?page=events" class="button button-lg radius-50 wow fadeInUp ms-3" data-wow-delay=".8s" style="background:#69b83b !important;border-color:#69b83b !important;">Événements <i class="lni lni-calendar"></i></a>
              </div>
            </div>
            <div class="col-lg-6 align-self-end">
              <div class="hero-image wow fadeInUp" data-wow-delay=".5s">
                <img src="assets/img/hero/hero-5/hero-img.svg" alt="DigiWork Hub illustration">
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- ========================= hero-section-wrapper-5 end ========================= -->

    <?php if ($renderSubPage): ?>
    <!-- Sub-page content rendered inside the same layout -->
    <?php if (!empty($pageStyles)) echo $pageStyles; ?>
    <div id="subpage-content">
      <?= $pageContent ?>
    </div>
    <?php else: ?>
    <section id="feature" class="feature-section feature-style-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-8">
            <div class="section-title text-center mb-60">
              <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Nos Services</h3>
              <p class="wow fadeInUp" data-wow-delay=".4s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
              <div class="icon">
                <i class="lni lni-vector"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg>                  
              </div>
              <div class="content">
                <h5>Graphics Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
              <div class="icon">
                <i class="lni lni-pallet"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Print Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".6s">
              <div class="icon">
                <i class="lni lni-stats-up"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Business Analysis</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
              <div class="icon">
                <i class="lni lni-code-alt"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Web Development</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
              <div class="icon">
                <i class="lni lni-lock"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Best Security</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".6s">
              <div class="icon">
                <i class="lni lni-code"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Web Design</h5>
                <p>Short description for the ones who look for something new.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- ========================= feature style-5 end ========================= -->

    <!-- ========================= about style-4 start ========================= -->
    <section id="about" class="about-section about-style-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-5 col-lg-6">
            <div class="about-content-wrapper">
              <div class="section-title mb-30">
                <h3 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Intelligence Artificielle &amp; Développement Durable</h3>
                <p class="wow fadeInUp" data-wow-delay=".3s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed,</p>
              </div>
              <ul>
                <li class="wow fadeInUp" data-wow-delay=".35s">
                  <i class="lni lni-checkmark-circle"></i>
                  Stop wasting time and money designing and managing a website that doesn’t get results.
                </li>
                <li class="wow fadeInUp" data-wow-delay=".4s">
                  <i class="lni lni-checkmark-circle"></i>
                  Stop wasting time and money designing and managing.
                </li>
                <li class="wow fadeInUp" data-wow-delay=".45s">
                  <i class="lni lni-checkmark-circle"></i>
                  Stop wasting time and money designing and managing a website that doesn’t get results.
                </li>
              </ul>
              <a href="#0" class="button button-lg radius-10 wow fadeInUp" data-wow-delay=".5s">Learn More</a>
            </div>
          </div>
          <div class="col-xl-7 col-lg-6">
            <div class="about-image text-lg-right wow fadeInUp" data-wow-delay=".5s">
              <img src="assets/img/about/about-4/about-img.svg" alt="">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= about style-4 end ========================= -->

    <!-- ========================= pricing style-4 start ========================= -->
    <section id="pricing" class="pricing-section pricing-style-4 bg-light">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-5 col-lg-6">
            <div class="section-title mb-60">
              <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Nos Packs</h3>
              <p class="wow fadeInUp" data-wow-delay=".4s">Choisissez le pack qui correspond parfaitement à l'échelle de vos projets et bénéficiez de tout le support nécessaire pour réussir.</p>
            </div>
          </div>
          <div class="col-xl-7 col-lg-6">
            <div class="pricing-active-wrapper wow fadeInUp" data-wow-delay=".4s">
              <div class="pricing-active">
                <?php
                require_once("model/Pack.php");
                $packModel = new Pack();
                $packs = $packModel->getAll();
                ?>
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
                    <button type="button" class="button radius-30" onclick="openModal('signupModal')">S'abonner</button>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= pricing style-4 end ========================= -->

    <!-- ========================= contact-style-3 start ========================= -->
    <section id="contact" class="contact-section contact-style-3">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-10">
            <div class="section-title text-center mb-50">
              <h3 class="mb-15">Contactez-nous</h3>
              <p>Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8">
            <div class="contact-form-wrapper">
              <form action="" method="">
                <div class="row">
                  <div class="col-md-6">
                    <div class="single-input">
                      <input type="text" id="name" name="name" class="form-input" placeholder="Name">
                      <i class="lni lni-user"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <input type="email" id="email" name="email" class="form-input" placeholder="Email">
                      <i class="lni lni-envelope"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <input type="text" id="number" name="number" class="form-input" placeholder="Number">
                      <i class="lni lni-phone"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <input type="text" id="subject" name="subject" class="form-input" placeholder="Subject">
                      <i class="lni lni-text-format"></i>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="single-input">
                      <textarea name="message" id="message" class="form-input" placeholder="Message" rows="6"></textarea>
                      <i class="lni lni-comments-alt"></i>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-button">
                      <button type="submit" class="button"> <i class="lni lni-telegram-original"></i> Submit</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>

          </div>

          <div class="col-lg-4">
            <div class="left-wrapper">
              <div class="row">
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-phone"></i>
                    </div>
                    <div class="text">
                      <p>0045939863784</p>
                      <p>+004389478327</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-envelope"></i>
                    </div>
                    <div class="text">
                      <p>yourmail@gmail.com</p>
                      <p>admin@yourwebsite.com</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-map-marker"></i>
                    </div>
                    <div class="text">
                      <p>John's House, 13/5 Road, Sidny United State Of America</p>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= contact-style-3 end ========================= -->

    <!-- ========================= clients-logo start ========================= -->
    <section class="clients-logo-section pt-100 pb-100">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="client-logo wow fadeInUp" data-wow-delay=".2s">
              <img src="assets/img/clients/brands.svg" alt="" class="w-100">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= clients-logo end ========================= -->

    <!-- ========================= footer style-4 start ========================= -->
    <footer class="footer footer-style-4">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="#0"> <img src="assets/img/logo/logo.png" alt="" style="height: 60px; width: auto;"> </a>
                </div>
                <p class="desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Facilisis nulla placerat amet amet congue.</p>
                <ul class="socials">
                  <li> <a href="#0"> <i class="lni lni-facebook-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-twitter-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-instagram-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-linkedin-original"></i> </a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-2 offset-xl-1 col-lg-2 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".3s">
                <h6>Quick Link</h6>
                <ul class="links">
                  <li> <a href="#0">Home</a> </li>
                  <li> <a href="#0">About</a> </li>
                  <li> <a href="#0">Service</a> </li>
                  <li> <a href="#0">Testimonial</a> </li>
                  <li> <a href="#0">Contact</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Services</h6>
                <ul class="links">
                  <li> <a href="#0">Web Design</a> </li>
                  <li> <a href="#0">Web Development</a> </li>
                  <li> <a href="#0">Seo Optimization</a> </li>
                  <li> <a href="#0">Blog Writing</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".5s">
                <h6>Download App</h6>
                <ul class="download-app">
                  <li>
                    <a href="#0">
                      <span class="icon"><i class="lni lni-apple"></i></span>
                      <span class="text">Download on the <b>App Store</b> </span>
                    </a>
                  </li>
                  <li>
                    <a href="#0">
                      <span class="icon"><i class="lni lni-play-store"></i></span>
                      <span class="text">GET IT ON <b>Play Store</b> </span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p>Design and Developed by <a href="https://uideck.com" rel="nofollow" target="_blank">UIdeck</a> Built-with <a href="https://uideck.com" rel="nofollow" target="_blank">Lindy UI Kit</a>. Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p>
        </div>
      </div>
    </footer>
    <!-- ========================= footer style-4 end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->

    <?php endif; // end else (landing page content) ?>

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="view/frontoffice/frontoffice-auth-forms.js"></script>
  </body>
</html>

