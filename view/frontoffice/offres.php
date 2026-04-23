<?php
require_once __DIR__ . '/../../controller/OffreController.php';
 
$controller = new OffreController();
$offres     = $controller->listOffre()->fetchAll(PDO::FETCH_ASSOC);
 
session_start();
$id_user = $_SESSION['id_user'] ?? 1;
 
$message     = "";
$messageType = "";
if (isset($_GET['status'], $_GET['msg'])) {
    $messageType = ($_GET['status'] === 'success') ? 'success' : 'danger';
    $message     = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Liste des offres - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/css/lindy-uikit.css" />
    <style>
        .offre-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e8eaf0;
            padding: 28px;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s;
        }
        .offre-card:hover {
            box-shadow: 0 12px 40px rgba(67,94,190,.13);
            transform: translateY(-4px);
        }
        .offre-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 14px;
        }
        .badge-CDI        { background:#e6f1fb; color:#185fa5; }
        .badge-CDD        { background:#faeeda; color:#854f0b; }
        .badge-Stage      { background:#e1f5ee; color:#0f6e56; }
        .badge-Freelance  { background:#eeedfe; color:#534ab7; }
        .badge-Alternance { background:#fbeaf0; color:#993556; }
        .badge-default    { background:#f1efe8; color:#5f5e5a; }
        .offre-title { font-size: 18px; font-weight: 700; color: #1a202c; margin-bottom: 10px; }
        .offre-desc  { font-size: 13px; color: #6c757d; line-height: 1.65; margin-bottom: 16px; flex-grow: 1; }
        .offre-meta  { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        .offre-meta-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #6c757d; }
        .comp-tag { background:#e1f5ee; color:#0f6e56; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; margin:2px; display:inline-block; }
        .offre-actions { display:flex; gap:10px; margin-top:auto; padding-top:16px; border-top:1px solid #f0f1f5; }
        .btn-postuler { background:#435ebe; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background .15s; flex:1; justify-content:center; }
        .btn-postuler:hover { background:#3348a8; }
        .btn-detail { background:#f5f6fa; color:#435ebe; border:1px solid #dce0f0; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:500; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
        .btn-detail:hover { background:#e8eaf5; color:#2d3a8c; text-decoration:none; }
 
        /* MODALE */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.52); z-index:9999; justify-content:center; align-items:center; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:#fff; border-radius:16px; width:100%; max-width:560px; max-height:92vh; overflow-y:auto; box-shadow:0 24px 64px rgba(0,0,0,.25); animation:modalIn .22s ease; }
        @keyframes modalIn { from{transform:translateY(-20px);opacity:0} to{transform:translateY(0);opacity:1} }
        .modal-top { display:flex; justify-content:space-between; align-items:center; padding:18px 24px 14px; border-bottom:1px solid #f0f1f5; }
        .modal-top h5 { margin:0; font-size:16px; font-weight:700; color:#2d3748; display:flex; align-items:center; gap:10px; }
        .modal-top h5 span { color:#435ebe; }
        .modal-icon-wrap { width:34px; height:34px; border-radius:8px; background:#e6f1fb; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .btn-close-x { background:#f5f6fa; border:none; border-radius:8px; width:32px; height:32px; font-size:18px; cursor:pointer; color:#888; display:flex; align-items:center; justify-content:center; transition:background .15s; }
        .btn-close-x:hover { background:#ffe5e5; color:#e74c3c; }
        .modal-body-inner { padding:20px 24px; }
        .modal-foot { padding:14px 24px; border-top:1px solid #f0f1f5; display:flex; justify-content:flex-end; gap:10px; background:#fafbfc; border-radius:0 0 16px 16px; }
        .field-label { font-size:11px; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; display:block; }
        .m-form-control { font-size:14px; border-radius:8px; border:1px solid #dee2e6; width:100%; padding:8px 12px; box-sizing:border-box; }
        .m-form-control:focus { border-color:#435ebe; outline:none; box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .m-form-control.is-invalid { border-color:#dc3545 !important; background:#fff8f8; }
        .error-message { font-size:12px; color:#dc3545; margin-top:4px; display:none; font-weight:500; }
        .error-message.show { display:block; }
        .section-divider { font-size:11px; font-weight:700; color:#adb5bd; text-transform:uppercase; letter-spacing:.08em; margin:4px 0 14px; display:flex; align-items:center; gap:8px; }
        .section-divider::after { content:''; flex:1; height:1px; background:#f0f1f5; }
        .btn-send { background:#435ebe; border:none; color:#fff; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:background .15s; }
        .btn-send:hover { background:#3348a8; }
        .btn-cancel-modal { background:#f5f6fa; border:none; color:#666; border-radius:8px; padding:10px 20px; font-size:14px; cursor:pointer; transition:background .15s; }
        .btn-cancel-modal:hover { background:#e8e9ef; }
        .info-auto { background:#e6f1fb; border-radius:8px; padding:10px 14px; font-size:12px; color:#185fa5; margin-top:4px; }
        .offres-hero { background:linear-gradient(135deg,#435ebe 0%,#263587 100%); padding:60px 0 50px; color:#fff; margin-bottom:40px; }
        .offres-hero h1 { font-size:32px; font-weight:800; margin-bottom:10px; }
        .offres-hero p  { font-size:15px; opacity:.85; margin:0; }
        .offres-count { font-size:13px; color:#6c757d; margin-bottom:20px; }
        .offres-count strong { color:#435ebe; }
        /* Compteur lettre */
        .char-counter { font-size:12px; color:#6c757d; margin-top:4px; }
        .char-counter.near-limit { color:#fd7e14; }
        .char-counter.at-limit   { color:#dc3545; font-weight:600; }
    </style>
</head>
<body>
 
<!-- HEADER -->
<header class="header header-6">
    <div class="navbar-area">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logo/logo.png" style="width:250px;">
                </a>
                <button class="navbar-toggler" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link active" href="offres.php">Offres</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="mes_candidatures.php">📋 Mes candidatures</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>
 
<!-- HERO -->
<section class="offres-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Toutes les offres disponibles</h1>
                <p>Découvrez les postes ouverts, les missions et les compétences recherchées.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div style="background:rgba(255,255,255,.15);border-radius:12px;padding:16px 24px;display:inline-block;text-align:center">
                    <div style="font-size:30px;font-weight:800"><?= count($offres) ?></div>
                    <div style="font-size:13px;opacity:.85">offre<?= count($offres) > 1 ? 's' : '' ?> disponible<?= count($offres) > 1 ? 's' : '' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
 
<!-- LISTE OFFRES -->
<section class="pb-5">
    <div class="container">
 
        <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4" role="alert">
            <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
 
        <?php if (!empty($offres)): ?>
        <p class="offres-count">
            Affichage de <strong><?= count($offres) ?></strong> offre<?= count($offres) > 1 ? 's' : '' ?>
        </p>
 
        <div class="row g-4">
            <?php foreach ($offres as $offre): ?>
            <div class="col-lg-6 col-xl-4">
                <div class="offre-card">
                    <?php
                    $type       = htmlspecialchars($offre['type']);
                    $badgeClass = in_array($type, ['CDI','CDD','Stage','Freelance','Alternance'])
                                ? 'badge-' . $type : 'badge-default';
                    ?>
                    <span class="offre-badge <?= $badgeClass ?>"><?= $type ?></span>
                    <div class="offre-title"><?= htmlspecialchars($offre['titre']) ?></div>
                    <div class="offre-desc">
                        <?= nl2br(htmlspecialchars(substr($offre['description'], 0, 150))) ?>...
                    </div>
                    <div class="mb-3">
                        <?php foreach (explode(',', $offre['competences']) as $comp): ?>
                        <span class="comp-tag"><?= htmlspecialchars(trim($comp)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="offre-meta">
                        <div class="offre-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <?= htmlspecialchars($offre['adresse']) ?>
                        </div>
                        <div class="offre-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <?= htmlspecialchars($offre['date_limite']) ?>
                        </div>
                    </div>
                    <div class="offre-actions">
                        <a href="detail_offre.php?id=<?= urlencode($offre['id_offer']) ?>" class="btn-detail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Détail
                        </a>
                        <button class="btn-postuler"
                            onclick='openPostulerModal(
                                <?= (int)$offre["id_offer"] ?>,
                                <?= json_encode($offre["titre"]) ?>
                            )'>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            Postuler
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
 
        <?php else: ?>
        <div class="alert alert-warning">Aucune offre n'est disponible pour le moment.</div>
        <?php endif; ?>
 
    </div>
</section>
 
<!-- ════════════════════════════════
     MODALE POSTULER
════════════════════════════════ -->
<div class="modal-overlay" id="postulerModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>
                <div class="modal-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#435ebe" stroke-width="2.2" stroke-linecap="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </div>
                Postuler —&nbsp;<span id="modal_titre_offre"></span>
            </h5>
            <button class="btn-close-x" onclick="closePostulerModal()">&#x2715;</button>
        </div>
 
        <form method="POST" action="postuler.php" id="postulerForm"
              enctype="multipart/form-data" onsubmit="return validatePostulerForm(event)">
 
            <!-- ✅ CORRIGÉ : name="id_offer" pour correspondre à postuler.php -->
            <input type="hidden" name="id_offer" id="modal_id_offre">
            <input type="hidden" name="id_user"  value="<?= (int)$id_user ?>">
 
            <div class="modal-body-inner">
                <div class="section-divider">Votre dossier de candidature</div>
 
                <!-- CV -->
                <div class="mb-3">
                    <label class="field-label">
                        📎 CV (PDF, DOC, DOCX) <span style="color:#dc3545">*</span>
                    </label>
                    <input type="file" class="m-form-control" name="cv"
                           id="input_cv" accept=".pdf,.doc,.docx">
                    <small style="color:#6c757d;font-size:12px">Taille max : 2 Mo</small>
                    <div class="error-message" id="cv_error"></div>
                </div>
 
                <!-- Lettre de motivation -->
                <div class="mb-3">
                    <label class="field-label">
                        📝 Lettre de motivation <span style="color:#dc3545">*</span>
                    </label>
                    <textarea class="m-form-control" name="lettre_motivation"
                              id="input_lettre" rows="6"
                              placeholder="Expliquez pourquoi vous êtes le candidat idéal pour ce poste..."
                              maxlength="2000"></textarea>
                    <!-- ✅ CORRIGÉ : 0 / 2000 caractères -->
                    <small class="char-counter" id="lettre_count">0 / 2000 caractères</small>
                    <div class="error-message" id="lettre_error"></div>
                </div>
 
                <div class="info-auto">
                    ℹ️ La date de candidature et le statut <strong>"en attente"</strong>
                    seront définis automatiquement.
                </div>
            </div>
 
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closePostulerModal()">Annuler</button>
                <button type="submit" class="btn-send">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Envoyer ma candidature
                </button>
            </div>
        </form>
    </div>
</div>
 
<script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
<script>
const MAX_LETTRE = 2000; // ✅ Limite correcte
 
/* ── Ouvrir modale ── */
function openPostulerModal(idOffre, titreOffre) {
    document.getElementById('modal_id_offre').value          = idOffre;
    document.getElementById('modal_titre_offre').textContent = titreOffre;
    clearErrors();
    document.getElementById('postulerForm').reset();
    // ✅ CORRIGÉ : affiche 0 / 2000
    document.getElementById('lettre_count').textContent = '0 / ' + MAX_LETTRE + ' caractères';
    document.getElementById('lettre_count').className = 'char-counter';
    document.getElementById('postulerModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
 
function closePostulerModal() {
    document.getElementById('postulerModal').classList.remove('show');
    document.body.style.overflow = '';
}
 
document.getElementById('postulerModal').addEventListener('click', function(e) {
    if (e.target === this) closePostulerModal();
});
 
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePostulerModal();
});
 
/* ── Compteur lettre ✅ CORRIGÉ ── */
document.getElementById('input_lettre').addEventListener('input', function() {
    var len     = this.value.length;
    var counter = document.getElementById('lettre_count');
    counter.textContent = len + ' / ' + MAX_LETTRE + ' caractères';
 
    // Couleur selon proximité de la limite
    counter.className = 'char-counter';
    if (len >= MAX_LETTRE)         counter.classList.add('at-limit');
    else if (len >= MAX_LETTRE * 0.9) counter.classList.add('near-limit');
});
 
/* ── Validation ── */
function clearErrors() {
    document.querySelectorAll('#postulerForm .m-form-control').forEach(function(i) {
        i.classList.remove('is-invalid');
    });
    document.querySelectorAll('#postulerForm .error-message').forEach(function(m) {
        m.classList.remove('show'); m.textContent = '';
    });
}
 
function setError(inputId, msg) {
    var input = document.getElementById(inputId);
    var err   = document.getElementById(inputId.replace('input_', '') + '_error');
    if (input) input.classList.add('is-invalid');
    if (err)   { err.textContent = msg; err.classList.add('show'); }
}
 
function validatePostulerForm(event) {
    event.preventDefault();
    clearErrors();
    var valid = true;
 
    /* CV */
    var cvInput = document.getElementById('input_cv');
    if (!cvInput.files || cvInput.files.length === 0) {
        setError('input_cv', 'Veuillez joindre votre CV');
        valid = false;
    } else {
        var file    = cvInput.files[0];
        var allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!allowed.includes(file.type)) {
            setError('input_cv', 'Format accepté : PDF, DOC, DOCX uniquement');
            valid = false;
        } else if (file.size > 2 * 1024 * 1024) {
            setError('input_cv', 'Le fichier ne doit pas dépasser 2 Mo');
            valid = false;
        }
    }
 
    /* Lettre */
    var lettre = document.getElementById('input_lettre').value.trim();
    if (!lettre) {
        setError('input_lettre', 'La lettre de motivation est obligatoire');
        valid = false;
    } else if (lettre.length < 50) {
        setError('input_lettre', 'Minimum 50 caractères requis');
        valid = false;
    }
 
    if (valid) document.getElementById('postulerForm').submit();
    return false;
}
</script>
</body>
</html>
 