<?php
session_start();
require_once __DIR__ . '/../../controller/OffreController.php';

$controller = new OffreController();
$offre = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $offre = $controller->getOffre((int) $_GET['id']);
}

// ── ID user fixe en attendant le login ──
$id_user = 1; // ← même valeur que dans postuler.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Détail de l'offre</title>
  <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
  <link rel="stylesheet" href="assets/css/LineIcons.2.0.css" />
  <link rel="stylesheet" href="assets/css/animate.css" />
  <link rel="stylesheet" href="assets/css/lindy-uikit.css" />
  <style>
    .postul-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(67,94,190,.10);
      padding: 32px 28px;
      margin-top: 10px;
    }
    .postul-title {
      font-size: 18px;
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .field-label {
      font-size: 12px;
      font-weight: 600;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: .04em;
      margin-bottom: 5px;
      display: block;
    }
    .form-control {
      border-radius: 8px;
      font-size: 14px;
      border: 1px solid #dee2e6;
      padding: 10px 14px;
      width: 100%;
      transition: border-color .2s;
    }
    .form-control:focus {
      outline: none;
      border-color: #435ebe;
      box-shadow: 0 0 0 3px rgba(67,94,190,.12);
    }
    .btn-postuler {
      background: #435ebe;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 11px 28px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      margin-top: 8px;
      transition: background .2s;
    }
    .btn-postuler:hover { background: #3348a8; }
    .alert-custom {
      border-radius: 10px;
      padding: 12px 18px;
      margin-bottom: 18px;
      font-size: 14px;
    }
    .alert-success { background: #d1e7dd; color: #0f5132; }
    .alert-danger  { background: #f8d7da; color: #842029; }
    .char-count { font-size: 12px; color: #adb5bd; margin-top: 4px; }
  </style>
</head>
<body>

  <header class="header header-6">
    <div class="navbar-area">
      <div class="container">
        <nav class="navbar navbar-expand-lg">
          <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo/logo.png" style="width:250px;">
          </a>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
              <li class="nav-item"><a class="nav-link" href="offres.php">Offres</a></li>
              <li class="nav-item"><a class="nav-link" href="mes_candidatures.php">Mes candidatures</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </div>
  </header>

  <main class="py-5">
    <div class="container">

      <?php if (isset($_GET['status'])): ?>
        <div class="alert-custom alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?>">
          <?= $_GET['status'] === 'success' ? '✅' : '❌' ?>
          <?= htmlspecialchars($_GET['msg'] ?? '') ?>
        </div>
      <?php endif; ?>

      <?php if ($offre): ?>
        <div class="row">

          <!-- ── Détail de l'offre ── -->
          <div class="col-lg-7 mb-4">
            <span class="badge bg-primary"><?= htmlspecialchars($offre['type']) ?></span>
            <h1 class="mt-3"><?= htmlspecialchars($offre['titre']) ?></h1>
            <p class="text-muted">📅 Date limite : <?= htmlspecialchars($offre['date_limite']) ?></p>
            <p class="text-muted">📍 <?= htmlspecialchars($offre['adresse']) ?></p>
            <div class="mb-4">
              <h5>Description</h5>
              <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
            </div>
            <div class="mb-4">
              <h5>Compétences requises</h5>
              <p><?= nl2br(htmlspecialchars($offre['competences'])) ?></p>
            </div>
            <a href="offres.php" class="button radius-50">← Retour à la liste</a>
          </div>

          <!-- ── Formulaire de postulation ── -->
          <div class="col-lg-5">
            <div class="postul-card">
              <div class="postul-title">📨 Postuler à cette offre</div>

              <form method="POST" action="postuler.php" enctype="multipart/form-data"
                    onsubmit="return validatePostuler(event)">

                <input type="hidden" name="id_offer" value="<?= (int)$offre['id_offer'] ?>">

                <!-- CV -->
                <div class="mb-3">
                  <label class="field-label">📎 CV <span style="color:red">*</span></label>
                  <input type="file" class="form-control" name="cv"
                         id="cv_input" accept=".pdf,.doc,.docx">
                  <small class="char-count">PDF, DOC, DOCX — max 2 Mo</small>
                  <div id="cv_error" style="color:#dc3545;font-size:12px;display:none"></div>
                </div>

                <!-- Lettre de motivation -->
                <div class="mb-3">
                  <label class="field-label">
                    📝 Lettre de motivation <span style="color:red">*</span>
                  </label>
                  <textarea class="form-control" name="lettre_motivation"
                            id="lettre_input" rows="6"
                            placeholder="Décrivez votre motivation (min. 50 caractères)..."
                            oninput="updateCount()"></textarea>
                  <div class="char-count" id="lettre_count">0 / 2000 caractères</div>
                  <div id="lettre_error" style="color:#dc3545;font-size:12px;display:none"></div>
                </div>

                <button type="submit" class="btn-postuler">
                  🚀 Envoyer ma candidature
                </button>

              </form>
            </div>
          </div>

        </div>

      <?php else: ?>
        <div class="alert alert-danger">Offre introuvable ou identifiant invalide.</div>
        <a href="offres.php" class="button radius-50">Retour à la liste</a>
      <?php endif; ?>

    </div>
  </main>

  <script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
  <script>
    function updateCount() {
      var len = document.getElementById('lettre_input').value.length;
      document.getElementById('lettre_count').textContent = len + ' / 2000 caractères';
    }

    function validatePostuler(e) {
      e.preventDefault();
      var ok = true;

      var cv = document.getElementById('cv_input');
      var cvErr = document.getElementById('cv_error');
      if (!cv.value) {
        cvErr.textContent = 'Veuillez joindre votre CV.';
        cvErr.style.display = 'block';
        ok = false;
      } else {
        cvErr.style.display = 'none';
      }

      var lettre = document.getElementById('lettre_input').value.trim();
      var letErr = document.getElementById('lettre_error');
      if (lettre.length < 50) {
        letErr.textContent = 'La lettre doit contenir au moins 50 caractères.';
        letErr.style.display = 'block';
        ok = false;
      } else {
        letErr.style.display = 'none';
      }

      if (ok) e.target.submit();
      return false;
    }
  </script>
</body>
</html>