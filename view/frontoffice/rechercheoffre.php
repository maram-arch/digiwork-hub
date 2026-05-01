<?php
header('Location: offres.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;

require_once __DIR__ . '/../../controller/OffreController.php';

$controller = new OffreController();

$titre       = trim($_GET['titre'] ?? '');
$competences = trim($_GET['competences'] ?? '');
$adresse     = trim($_GET['adresse'] ?? '');

$hasSearch = ($titre !== '' || $competences !== '' || $adresse !== '');
$offres = $hasSearch
    ? $controller->searchOffre($titre, $competences, $adresse)->fetchAll(PDO::FETCH_ASSOC)
    : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recherche des offres - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/css/lindy-uikit.css" />
    <style>
        .search-hero { background:linear-gradient(135deg,#435ebe 0%,#263587 100%); padding:58px 0 46px; color:#fff; margin-bottom:34px; }
        .search-hero h1 { font-size:32px; font-weight:800; margin-bottom:10px; }
        .search-hero p { font-size:15px; opacity:.86; margin:0; }
        .search-box { background:#fff; border:1px solid #e8eaf0; border-radius:12px; padding:22px; box-shadow:0 12px 32px rgba(67,94,190,.08); margin-top:-58px; margin-bottom:30px; position:relative; z-index:2; }
        .field-label { font-size:11px; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; display:block; }
        .search-control { border:1px solid #dee2e6; border-radius:8px; padding:10px 12px; width:100%; font-size:14px; }
        .search-control:focus { border-color:#435ebe; outline:none; box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .btn-search { background:#435ebe; color:#fff; border:none; border-radius:8px; padding:10px 22px; font-size:14px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:7px; width:100%; }
        .btn-search:hover { background:#3348a8; color:#fff; }
        .offres-count { font-size:13px; color:#6c757d; margin-bottom:20px; }
        .offres-count strong { color:#435ebe; }
        .offre-card { background:#fff; border-radius:16px; border:1px solid #e8eaf0; padding:28px; height:100%; display:flex; flex-direction:column; transition:box-shadow .2s, transform .2s; }
        .offre-card:hover { box-shadow:0 12px 40px rgba(67,94,190,.13); transform:translateY(-4px); }
        .offre-badge { display:inline-block; padding:4px 14px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.04em; margin-bottom:14px; }
        .badge-CDI { background:#e6f1fb; color:#185fa5; }
        .badge-CDD { background:#faeeda; color:#854f0b; }
        .badge-Stage { background:#e1f5ee; color:#0f6e56; }
        .badge-Freelance { background:#eeedfe; color:#534ab7; }
        .badge-Alternance { background:#fbeaf0; color:#993556; }
        .badge-default { background:#f1efe8; color:#5f5e5a; }
        .offre-title { font-size:18px; font-weight:700; color:#1a202c; margin-bottom:10px; }
        .offre-desc { font-size:13px; color:#6c757d; line-height:1.65; margin-bottom:16px; flex-grow:1; }
        .offre-meta { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; }
        .offre-meta-item { display:flex; align-items:center; gap:5px; font-size:12px; color:#6c757d; }
        .comp-tag { background:#e1f5ee; color:#0f6e56; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; margin:2px; display:inline-block; }
        .btn-detail { background:#f5f6fa; color:#435ebe; border:1px solid #dce0f0; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; width:max-content; }
        .btn-detail:hover { background:#e8eaf5; color:#2d3a8c; text-decoration:none; }
    </style>
</head>
<body>

<header class="header header-6">
    <div class="navbar-area">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logo/logo.png" style="width:250px;" alt="DigiWork Hub">
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
                        <li class="nav-item"><a class="nav-link" href="offres.php">Offres</a></li>
                        <li class="nav-item"><a class="nav-link active" href="rechercheoffre.php">Recherche offre</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_candidatures.php">Mes candidatures</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>

<section class="search-hero">
    <div class="container">
        <h1>Rechercher une offre</h1>
        <p>Trouvez rapidement une offre par titre, competences ou adresse.</p>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <form class="search-box" method="GET" action="rechercheoffre.php">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="field-label" for="titre">Titre</label>
                    <input class="search-control" type="text" id="titre" name="titre"
                           placeholder="Ex: Developpeur web"
                           value="<?= htmlspecialchars($titre) ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label" for="competences">Competences</label>
                    <input class="search-control" type="text" id="competences" name="competences"
                           placeholder="Ex: PHP, JavaScript"
                           value="<?= htmlspecialchars($competences) ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label" for="adresse">Adresse</label>
                    <input class="search-control" type="text" id="adresse" name="adresse"
                           placeholder="Ex: Tunis"
                           value="<?= htmlspecialchars($adresse) ?>">
                </div>
                <div class="col-md-3">
                    <button class="btn-search" type="submit">Rechercher</button>
                </div>
                <div class="col-md-9 text-md-end">
                    <a href="offres.php" class="btn-detail">Voir toutes les offres</a>
                </div>
            </div>
        </form>

        <?php if ($hasSearch): ?>
            <p class="offres-count">
                <strong><?= count($offres) ?></strong> resultat<?= count($offres) > 1 ? 's' : '' ?> trouve<?= count($offres) > 1 ? 's' : '' ?>
            </p>

            <?php if (!empty($offres)): ?>
                <div class="row g-4">
                    <?php foreach ($offres as $offre): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="offre-card">
                                <?php
                                $type = htmlspecialchars($offre['type'] ?? 'Offre');
                                $badgeClass = in_array($type, ['CDI','CDD','Stage','Freelance','Alternance'])
                                    ? 'badge-' . $type
                                    : 'badge-default';
                                ?>
                                <span class="offre-badge <?= $badgeClass ?>"><?= $type ?></span>
                                <div class="offre-title"><?= htmlspecialchars($offre['titre'] ?? '') ?></div>
                                <div class="offre-desc">
                                    <?= nl2br(htmlspecialchars(substr($offre['description'] ?? '', 0, 150))) ?>...
                                </div>
                                <div class="mb-3">
                                    <?php foreach (explode(',', $offre['competences'] ?? '') as $comp): ?>
                                        <?php if (trim($comp) !== ''): ?>
                                            <span class="comp-tag"><?= htmlspecialchars(trim($comp)) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="offre-meta">
                                    <div class="offre-meta-item">
                                        <span>Adresse :</span>
                                        <?= htmlspecialchars($offre['adresse'] ?? '') ?>
                                    </div>
                                    <div class="offre-meta-item">
                                        <span>Date limite :</span>
                                        <?= htmlspecialchars($offre['date_limite'] ?? '') ?>
                                    </div>
                                </div>
                                <a href="detail_offre.php?id=<?= urlencode($offre['id_offer']) ?>" class="btn-detail">Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Aucune offre ne correspond a votre recherche.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">Saisissez au moins un critere pour lancer la recherche.</div>
        <?php endif; ?>
    </div>
</section>

<script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
<?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>
