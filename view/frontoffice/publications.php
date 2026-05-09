<?php
// view/frontoffice/publications.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;
}

require_once __DIR__ . '/../../model/Publication.php';
require_once __DIR__ . '/../../model/Recommendation.php';
require_once __DIR__ . '/../../config/config.php';

// ========== AJAX LIKES & FAVORIS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $id_user = $_SESSION['id_user'] ?? 0;
    if (!$id_user) { echo json_encode(['success' => false]); exit; }
    $id_pub = (int)($_POST['id_publication'] ?? 0);

    if ($_GET['action'] === 'like') {
        $result = Publication::toggleLike($id_pub, $id_user);
        // Enregistrer l'interaction
        if ($result['action'] === 'liked') {
            Recommendation::logInteraction($id_user, $id_pub, 'like');
        }
        echo json_encode(['success' => true, 'action' => $result['action'], 'nb_likes' => $result['nb_likes']]);
        exit;
    } elseif ($_GET['action'] === 'favori') {
        if (Publication::isFavori($id_pub, $id_user)) {
            Publication::removeFavori($id_pub, $id_user);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            Publication::addFavori($id_pub, $id_user);
            Recommendation::logInteraction($id_user, $id_pub, 'favori');
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }
}

// ========== ENREGISTRER LA VUE de la liste ==========
// (Les vues individuelles sont gérées dans detail_publication.php)

// Paramètres d'affichage
$categorie  = $_GET['categorie'] ?? 'all';
$tri        = $_GET['tri'] ?? 'date';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 6;
$id_user    = $_SESSION['id_user'];

$publications = Publication::getAllWithFilters($categorie, $tri, $page, $perPage);
$total        = Publication::countWithFilters($categorie);
$totalPages   = ceil($total / $perPage);

// ========== RECOMMANDATIONS ==========
$recommandations = Recommendation::getRecommendations($id_user, 4);
$hasHistory      = !empty(
    (function() use ($id_user) {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_interactions WHERE id_user = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetchColumn();
    })()
);

$likedIds  = [];
$favoriIds = [];
foreach ($publications as $p) {
    if (Publication::hasLiked($p['id_publication'], $id_user))   $likedIds[]  = $p['id_publication'];
    if (Publication::isFavori($p['id_publication'], $id_user))   $favoriIds[] = $p['id_publication'];
}

function tronquer($txt, $len = 100) {
    return strlen($txt) <= $len ? $txt : substr($txt, 0, strrpos(substr($txt, 0, $len), ' ')) . '…';
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <style>
        body { background: #f0f2f9; transition: 0.3s; }
        body.dark-mode { background: #1e1e2f; color: #f0f0f0; }
        body.dark-mode .pub-card,
        body.dark-mode .reco-card,
        body.dark-mode .filter-form,
        body.dark-mode .navbar { background: #2a2a3a; color: #f0f0f0; border-color: #444; }
        body.dark-mode .pub-card *,
        body.dark-mode .reco-card * { color: #f0f0f0; }
        body.dark-mode .text-muted, body.dark-mode small { color: #bbbbbb !important; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #3a3a4a; color: #fff; border-color: #555; }
        body.dark-mode .reco-section { background: #23233a; border-color: #3a3a5a; }

        /* ===== CARTES PUBLICATIONS ===== */
        .pub-card { background: #fff; border-radius: 20px; border: 1px solid #e8eaf0; padding: 24px; height: 100%; transition: 0.2s; }
        .pub-card:hover { box-shadow: 0 5px 20px rgba(67,94,190,0.12); transform: translateY(-2px); }
        .like-btn, .favori-btn { background: none; border: none; cursor: pointer; color: #6c757d; }
        .like-btn.liked { color: #e74c3c; }
        .favori-btn.active { color: #f1c40f; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: #435ebe; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; z-index: 1000; }

        /* ===== SECTION RECOMMANDATIONS ===== */
        .reco-section {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            border: 1px solid #d0dcf8;
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 40px;
        }
        .reco-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #323450;
            margin-bottom: 6px;
        }
        .reco-section-subtitle {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .reco-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e0e7ff;
            padding: 18px;
            height: 100%;
            transition: 0.2s;
            position: relative;
        }
        .reco-card:hover { box-shadow: 0 5px 20px rgba(67,94,190,0.15); transform: translateY(-2px); }
        .reco-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(90deg, #435ebe, #263587);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }
        .reco-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #323450;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        .reco-card-meta {
            font-size: 11px;
            color: #888;
            margin-bottom: 8px;
        }
        .reco-card-excerpt {
            font-size: 12px;
            color: #666;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .reco-score {
            position: absolute;
            top: 12px;
            right: 14px;
            font-size: 10px;
            color: #435ebe;
            font-weight: 600;
            background: #eef1ff;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .reco-empty {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 14px;
        }

        /* Dark mode reco */
        body.dark-mode .reco-section { background: #1e2540; border-color: #2d3a6e; }
        body.dark-mode .reco-section-title { color: #e0e6ff; }
        body.dark-mode .reco-card { background: #252a45; border-color: #2d3a6e; }
        body.dark-mode .reco-card-title { color: #d0d8ff; }
        body.dark-mode .reco-card-meta { color: #8899bb; }
        body.dark-mode .reco-card-excerpt { color: #9aaacc; }
        body.dark-mode .reco-score { background: #1a2035; color: #7090f0; }
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
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="toggler-icon"></span><span class="toggler-icon"></span><span class="toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link active" href="publications.php">Forum</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_commentaires.php">Mes commentaires</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_favoris.php">⭐ Mes favoris</a></li>
                        <li class="nav-item"><a class="nav-link" href="statistiques.php">Statistiques</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>

<!-- Hero -->
<section class="hero" style="background:linear-gradient(135deg,#435ebe 0%,#263587 100%);padding:50px 0;color:white;margin-bottom:40px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Forum DigiWork Hub</h1>
                <p>Partagez vos idées, posez vos questions.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white text-dark rounded p-3 d-inline-block">
                    <div style="font-size:30px;font-weight:800;"><?= $total ?></div>
                    <div>publication<?= $total > 1 ? 's' : '' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container pb-5">

    <!-- ===== SECTION RECOMMANDATIONS IA ===== -->
    <?php if (!empty($recommandations)): ?>
    <div class="reco-section">
        <div class="reco-section-title">
            🤖 Publications recommandées pour toi
        </div>
        <div class="reco-section-subtitle">
            <?php if ($hasHistory): ?>
                Basées sur tes interactions récentes
            <?php else: ?>
                Les plus populaires du moment — interagis avec des publications pour des recommandations personnalisées
            <?php endif; ?>
        </div>

        <div class="row g-3">
            <?php foreach ($recommandations as $reco): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="reco-card">
                        <?php if (!empty($reco['score'])): ?>
                            <div class="reco-score">
                                <?= round($reco['score'] * 100) ?>% match
                            </div>
                        <?php endif; ?>

                        <div class="reco-badge">✨ Recommandé</div>

                        <div class="reco-card-title">
                            <?= htmlspecialchars($reco['titre']) ?>
                        </div>

                        <div class="reco-card-meta">
                            <span class="badge bg-primary" style="font-size:10px;">
                                <?= htmlspecialchars($reco['categorie']) ?>
                            </span>
                            &nbsp;
                            <?= htmlspecialchars(($reco['prenom'] ?? '') . ' ' . ($reco['nom'] ?? '')) ?>
                            · <?= date('d/m/Y', strtotime($reco['date_publication'])) ?>
                        </div>

                        <div class="reco-card-excerpt">
                            <?= htmlspecialchars(tronquer($reco['contenu'], 80)) ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small>❤️ <?= (int)$reco['nb_likes'] ?> &nbsp; 👁️ <?= (int)$reco['nb_vues'] ?></small>
                            <a href="detail_publication.php?id=<?= $reco['id_publication'] ?>"
                               class="btn btn-sm btn-primary" style="font-size:11px;">
                                Lire →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== PUBLICATIONS PRINCIPALES ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📢 Dernières publications</h2>
        <a href="ajouter_publication.php" class="btn btn-success">➕ Nouvelle publication</a>
    </div>

    <!-- Filtres -->
    <div class="filter-form bg-white p-3 rounded mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="categorie" class="form-select">
                    <option value="all"       <?= $categorie=='all'       ?'selected':'' ?>>Toutes catégories</option>
                    <option value="general"   <?= $categorie=='general'   ?'selected':'' ?>>Général</option>
                    <option value="stage"     <?= $categorie=='stage'     ?'selected':'' ?>>Stage</option>
                    <option value="job"       <?= $categorie=='job'       ?'selected':'' ?>>Job</option>
                    <option value="question"  <?= $categorie=='question'  ?'selected':'' ?>>Question</option>
                    <option value="evenement" <?= $categorie=='evenement' ?'selected':'' ?>>Événement</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="tri" class="form-select">
                    <option value="date"  <?= $tri=='date'  ?'selected':'' ?>>Date récente</option>
                    <option value="likes" <?= $tri=='likes' ?'selected':'' ?>>Plus de likes</option>
                    <option value="vues"  <?= $tri=='vues'  ?'selected':'' ?>>Plus de vues</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Appliquer</button>
            </div>
        </form>
    </div>

    <!-- Liste publications -->
    <?php if (!empty($publications)): ?>
        <div class="row g-4">
            <?php foreach ($publications as $pub): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="pub-card">
                        <?php if ($pub['image']): ?>
                            <img src="../../<?= htmlspecialchars($pub['image']) ?>"
                                 class="w-100 rounded mb-2" style="max-height:150px;object-fit:cover;">
                        <?php endif; ?>

                        <div>
                            <span class="badge bg-primary"><?= htmlspecialchars($pub['categorie']) ?></span>
                            <?php if ($pub['is_event']): ?>
                                <span class="badge bg-warning ms-1">🎉 Événement</span>
                            <?php endif; ?>
                        </div>

                        <h5 class="mt-2"><?= htmlspecialchars($pub['titre']) ?></h5>
                        <p class="small text-muted">
                            <?= htmlspecialchars(($pub['prenom'] ?? '') . ' ' . ($pub['nom'] ?? '')) ?>
                            - <?= date('d/m/Y', strtotime($pub['date_publication'])) ?>
                        </p>
                        <p><?= nl2br(htmlspecialchars(tronquer($pub['contenu'], 100))) ?></p>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button class="like-btn <?= in_array($pub['id_publication'], $likedIds) ? 'liked' : '' ?>"
                                        data-id="<?= $pub['id_publication'] ?>">
                                    ❤️ <span class="like-count"><?= (int)$pub['nb_likes'] ?></span>
                                </button>
                                <button class="favori-btn ms-2 <?= in_array($pub['id_publication'], $favoriIds) ? 'active' : '' ?>"
                                        data-id="<?= $pub['id_publication'] ?>">⭐</button>
                            </div>
                            <span>👁️ <?= (int)$pub['nb_vues'] ?></span>
                        </div>

                        <div class="mt-3">
                            <a href="detail_publication.php?id=<?= $pub['id_publication'] ?>"
                               class="btn btn-sm btn-outline-primary w-100">Lire la suite</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?categorie=<?= urlencode($categorie) ?>&tri=<?= urlencode($tri) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning text-center">Aucune publication trouvée.</div>
    <?php endif; ?>

</div>

<button id="themeToggle" class="theme-toggle">🌓</button>

<script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
<script>
// Like
document.querySelectorAll('.like-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var self = this;
        fetch('publications.php?action=like', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=' + id
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                self.querySelector('.like-count').innerText = d.nb_likes;
                if (d.action === 'liked') self.classList.add('liked');
                else self.classList.remove('liked');
            }
        });
    });
});

// Favori
document.querySelectorAll('.favori-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var self = this;
        fetch('publications.php?action=favori', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=' + id
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                if (d.action === 'added') self.classList.add('active');
                else self.classList.remove('active');
            }
        });
    });
});

// Dark mode
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
</script>

</body>
</html>