<?php
// view/frontoffice/detail_publication.php

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
require_once __DIR__ . '/../../model/Commentaire.php';
require_once __DIR__ . '/../../controller/CommentaireController.php';
require_once __DIR__ . '/../../config/config.php';

$id_pub = (int)($_GET['id'] ?? 0);
if (!$id_pub) {
    header('Location: publications.php?status=error&msg=ID+publication+manquant');
    exit;
}

// Traitements AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'likePub') {
        header('Content-Type: application/json');
        $id_user = $_SESSION['id_user'];
        $id_publication = (int)($_POST['id_publication'] ?? 0);
        if (!$id_publication) {
            echo json_encode(['success' => false, 'message' => 'ID publication manquant']);
            exit;
        }
        $result = Publication::toggleLike($id_publication, $id_user);
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT nb_likes FROM forums WHERE id_publication = ?");
        $stmt->execute([$id_publication]);
        $nb_likes = (int)$stmt->fetchColumn();
        echo json_encode(['success' => true, 'action' => $result['action'], 'nb_likes' => $nb_likes]);
        exit;
    }
    if ($action === 'likeCommentaire') {
        $ctrl = new CommentaireController();
        $ctrl->likeCommentaireAjax();
        exit;
    }
    if ($action === 'addReponse') {
        $ctrl = new CommentaireController();
        $ctrl->addReponseAjax();
        exit;
    }
}

Publication::incrementVues($id_pub, $_SESSION['id_user'] ?? null);

$pub = Publication::getByIdWithUser($id_pub);
if (!$pub) {
    header('Location: publications.php?status=error&msg=Publication+introuvable');
    exit;
}

$commentaires = Commentaire::getTreeByPublication($id_pub);

$hasLiked = false;
$isFavori = false;
$likedCommentsIds = [];
if (isset($_SESSION['id_user'])) {
    $hasLiked = Publication::hasLiked($id_pub, $_SESSION['id_user']);
    $isFavori = Publication::isFavori($id_pub, $_SESSION['id_user']);
    $allComments = Commentaire::getByPublication($id_pub);
    foreach ($allComments as $c) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM commentaire_likes WHERE id_commentaire = ? AND id_user = ?");
        $stmt->execute([$c['id_commentaire'], $_SESSION['id_user']]);
        if ($stmt->fetchColumn()) {
            $likedCommentsIds[] = $c['id_commentaire'];
        }
    }
}

function renderCommentTree($comments, $likedIds, $level = 0) {
    $html = '';
    foreach ($comments as $c) {
        $isLiked = in_array($c['id_commentaire'], $likedIds);
        $html .= '<div class="comment-card" style="margin-left: ' . ($level * 20) . 'px;' . ($level ? 'border-left: 3px solid #435ebe; padding-left: 15px;' : '') . '">';
        $html .= '<div class="d-flex justify-content-between"><div><strong>' . htmlspecialchars($c['prenom'] . ' ' . $c['nom']) . '</strong>';
        $html .= ' <small class="text-muted">le ' . htmlspecialchars($c['date_commentaire']) . '</small></div></div>';
        $html .= '<p class="mt-2">' . nl2br(htmlspecialchars($c['contenu'])) . '</p>';
        $html .= '<div class="d-flex gap-3 align-items-center">';
        $html .= '<button class="like-comment-btn ' . ($isLiked ? 'liked' : '') . '" data-id="' . $c['id_commentaire'] . '">❤️ <span class="like-count">' . (int)($c['total_likes'] ?? 0) . '</span></button>';
        $html .= '<button class="btn-repondre" data-id="' . $c['id_commentaire'] . '">💬 Répondre</button></div>';
        $html .= '<div id="reponse-form-' . $c['id_commentaire'] . '" class="reponse-form mt-2" style="display:none;">';
        $html .= '<form class="form-reponse" data-parent="' . $c['id_commentaire'] . '">';
        $html .= '<textarea name="contenu" rows="2" class="form-control" placeholder="Votre réponse..."></textarea>';
        $html .= '<div class="mt-1"><button type="submit" class="btn btn-sm btn-primary">Envoyer</button> ';
        $html .= '<button type="button" class="btn btn-sm btn-secondary annuler-reponse">Annuler</button></div></form></div>';
        if (!empty($c['reponses'])) {
            $html .= '<div class="reponses mt-3">' . renderCommentTree($c['reponses'], $likedIds, $level + 1) . '</div>';
        }
        $html .= '</div>';
    }
    return $html;
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pub['titre']) ?> - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <style>
        body { background: #f0f2f9; transition: 0.3s; }
        body.dark-mode { background: #1e1e2f; color: #eee; }
        body.dark-mode .pub-card,
        body.dark-mode .comment-card,
        body.dark-mode .navbar,
        body.dark-mode .card-pub { background: #2a2a3a; color: #eee; border-color: #444; }
        body.dark-mode .like-btn,
        body.dark-mode .favori-btn { color: #ccc; }
        body.dark-mode .like-btn.liked,
        body.dark-mode .favori-btn.active { color: #f1c40f; }

        .card-pub { background: #fff; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .comment-card { background: #f8f9fa; border-radius: 12px; padding: 15px; margin-bottom: 15px; }
        .like-btn, .like-comment-btn, .favori-btn { background: none; border: none; cursor: pointer; color: #6c757d; transition: 0.2s; }
        .like-btn.liked, .like-comment-btn.liked { color: #e74c3c; }
        .favori-btn.active { color: #f1c40f; }
        .btn-repondre { background: none; border: none; color: #435ebe; font-size: 13px; cursor: pointer; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: #435ebe; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; z-index: 1000; }

        /* ===== ASSISTANT IA ===== */
        .ai-toolbar-comment {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .ai-btn-comment {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f0f6ff;
            color: #2F80ED;
            border: 1px solid #c6dcf9;
            border-radius: 20px;
            padding: 4px 13px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        .ai-btn-comment:hover { background: #dceeff; transform: translateY(-1px); }
        .ai-btn-comment.loading { opacity: 0.6; pointer-events: none; }

        .ai-suggestion-box {
            display: none;
            margin-top: 10px;
            background: #f8fbff;
            border: 1px solid #c6dcf9;
            border-radius: 8px;
            padding: 13px 15px;
        }
        .ai-suggestion-box .ai-label {
            font-size: 11px;
            font-weight: 700;
            color: #2F80ED;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }
        .ai-suggestion-box .ai-text {
            font-size: 13px;
            color: #323450;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .ai-suggestion-box .ai-actions {
            display: flex;
            gap: 7px;
            margin-top: 9px;
        }
        .ai-accept-btn {
            background: #2F80ED; color: #fff; border: none;
            border-radius: 5px; padding: 5px 15px;
            font-size: 12px; font-weight: 600; cursor: pointer;
        }
        .ai-accept-btn:hover { background: #1a6fd4; }
        .ai-discard-btn {
            background: transparent; color: #888;
            border: 1px solid #ddd; border-radius: 5px;
            padding: 5px 15px; font-size: 12px; cursor: pointer;
        }
        .ai-discard-btn:hover { color: #dc3545; border-color: #dc3545; }

        .ai-spinner {
            display: inline-block; width: 10px; height: 10px;
            border: 2px solid #2F80ED; border-top-color: transparent;
            border-radius: 50%; animation: ai-spin 0.7s linear infinite;
            vertical-align: middle;
        }
        @keyframes ai-spin { to { transform: rotate(360deg); } }

        /* Dark mode pour la suggestion box */
        body.dark-mode .ai-suggestion-box { background: #1e2a3a; border-color: #2d4a6e; }
        body.dark-mode .ai-suggestion-box .ai-text { color: #cdd; }
        body.dark-mode .ai-btn-comment { background: #1e2a3a; border-color: #2d4a6e; color: #5ba3f5; }
        body.dark-mode .ai-btn-comment:hover { background: #243450; }
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
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="publications.php">Forum</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_commentaires.php">Mes commentaires</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_favoris.php">⭐ Mes favoris</a></li>
                        <li class="nav-item"><a class="nav-link" href="statistiques.php">Statistiques</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>

<div class="container py-4">

    <?php if (isset($_GET['status'], $_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <!-- ===== CARTE PUBLICATION ===== -->
    <div class="card-pub">
        <div class="d-flex justify-content-between">
            <div>
                <span class="badge bg-primary"><?= htmlspecialchars($pub['categorie']) ?></span>
                <?php if ($pub['is_event']): ?>
                    <span class="badge bg-warning ms-1">🎉 Événement</span>
                <?php endif; ?>
            </div>
            <?php if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $pub['id_user']): ?>
                <div>
                    <a href="editPublication.php?id=<?= $id_pub ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="mt-3"><?= htmlspecialchars($pub['titre']) ?></h1>
        <p class="text-muted">
            Par <strong><?= htmlspecialchars($pub['prenom'] . ' ' . $pub['nom']) ?></strong>
            – le <?= date('d/m/Y H:i', strtotime($pub['date_publication'])) ?>
        </p>

        <?php if (!empty($pub['image'])): ?>
            <img src="../../<?= htmlspecialchars($pub['image']) ?>"
                 class="img-fluid rounded mb-3" style="max-height:300px;">
        <?php endif; ?>

        <div class="content mb-4"><?= nl2br(htmlspecialchars($pub['contenu'])) ?></div>

        <div class="d-flex gap-3 align-items-center">
            <button class="like-btn <?= $hasLiked ? 'liked' : '' ?>" data-id="<?= $id_pub ?>">
                ❤️ <span class="like-count"><?= (int)$pub['nb_likes'] ?></span>
            </button>
            <button class="favori-btn <?= $isFavori ? 'active' : '' ?>" data-id="<?= $id_pub ?>">
                ⭐ Favori
            </button>
            <span>👁️ <?= (int)$pub['nb_vues'] ?> vues</span>
            <a href="export_pdf.php?id=<?= $id_pub ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                📄 Exporter PDF
            </a>
        </div>
    </div>

    <!-- ===== COMMENTAIRES ===== -->
    <div class="card-pub">
        <h4>💬 Commentaires</h4>

        <div id="comments-container">
            <?php if (empty($commentaires)): ?>
                <p class="text-muted">Soyez le premier à commenter !</p>
            <?php else: ?>
                <?= renderCommentTree($commentaires, $likedCommentsIds) ?>
            <?php endif; ?>
        </div>

        <!-- ===== FORMULAIRE COMMENTAIRE + BOUTONS IA ===== -->
        <div class="mt-4">
            <h5>Ajouter un commentaire</h5>
            <form id="form-commentaire">

                <textarea name="contenu" id="comment-contenu" class="form-control" rows="3"
                    placeholder="Votre commentaire (min 3 caractères)"></textarea>

                <!-- Barre boutons IA -->
                <div class="ai-toolbar-comment">
                    <button type="button" class="ai-btn-comment"
                            onclick="aiComment('ameliorer', this)">
                        ✨ Améliorer
                    </button>
                    <button type="button" class="ai-btn-comment"
                            onclick="aiComment('corriger', this)">
                        ✨ Corriger
                    </button>
                    <button type="button" class="ai-btn-comment"
                            onclick="aiComment('resumer', this)">
                        ✨ Résumer
                    </button>
                </div>

                <!-- Zone de suggestion IA -->
                <div class="ai-suggestion-box" id="ai-comment-suggestion">
                    <div class="ai-label">✨ Suggestion IA</div>
                    <div class="ai-text" id="ai-comment-suggestion-text"></div>
                    <div class="ai-actions">
                        <button type="button" class="ai-accept-btn"
                                onclick="acceptCommentSuggestion()">
                            ✅ Utiliser ce texte
                        </button>
                        <button type="button" class="ai-discard-btn"
                                onclick="document.getElementById('ai-comment-suggestion').style.display='none'">
                            ✕ Ignorer
                        </button>
                    </div>
                </div>

                <div id="commentError" class="text-danger small mt-1"></div>
                <button type="submit" class="btn btn-primary mt-2">Poster</button>
            </form>
        </div>
    </div>

</div>

<button id="themeToggle" class="theme-toggle">🌓</button>

<script src="assets/js/bootstrap-5.0.0-beta1.min.js"></script>
<script>
// ===== LIKE PUBLICATION =====
document.querySelectorAll('.like-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var self = this;
        fetch(window.location.pathname + '?action=likePub', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=' + id
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                self.querySelector('.like-count').innerText = data.nb_likes;
                if (data.action === 'liked') self.classList.add('liked');
                else self.classList.remove('liked');
            }
        });
    });
});

// ===== LIKE COMMENTAIRE =====
document.querySelectorAll('.like-comment-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var btnEl = this;
        fetch('?action=likeCommentaire', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_commentaire=' + id
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var span = btnEl.querySelector('.like-count');
                if (span) span.innerText = data.nb_likes;
                if (data.action === 'liked') btnEl.classList.add('liked');
                else btnEl.classList.remove('liked');
            } else {
                alert('Erreur: ' + (data.message || 'inconnue'));
            }
        })
        .catch(function(err) { console.error(err); });
    });
});

// ===== FAVORI =====
document.querySelectorAll('.favori-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var self = this;
        fetch('publications.php?action=favori', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=' + id
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.action === 'added') self.classList.add('active');
                else self.classList.remove('active');
            }
        });
    });
});

// ===== RÉPONDRE =====
document.querySelectorAll('.btn-repondre').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var formDiv = document.getElementById('reponse-form-' + id);
        formDiv.style.display = formDiv.style.display === 'none' ? 'block' : 'none';
    });
});
document.querySelectorAll('.annuler-reponse').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var formDiv = this.closest('.reponse-form');
        if (formDiv) formDiv.style.display = 'none';
    });
});

// ===== SOUMISSION RÉPONSE =====
document.querySelectorAll('.form-reponse').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var parentId = this.dataset.parent;
        var textarea = this.querySelector('textarea[name="contenu"]');
        var contenu = textarea.value.trim();
        if (contenu.length < 3) { alert('Minimum 3 caractères.'); return; }
        fetch(window.location.pathname + '?action=addReponse', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_publication=<?= $id_pub ?>&parent_id=' + parentId + '&contenu=' + encodeURIComponent(contenu)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) location.reload();
            else alert('Erreur lors de l\'ajout de la réponse.');
        });
    });
});

// ===== AJOUT COMMENTAIRE RACINE =====
document.getElementById('form-commentaire').addEventListener('submit', function(e) {
    e.preventDefault();
    var contenu = document.getElementById('comment-contenu').value.trim();
    var errDiv = document.getElementById('commentError');
    if (contenu.length < 3) { errDiv.innerText = 'Minimum 3 caractères.'; return; }
    errDiv.innerText = '';
    fetch('ajouter_commentaire.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_publication=<?= $id_pub ?>&contenu=' + encodeURIComponent(contenu) + '&parent_id=0'
    })
    .then(function() { location.reload(); });
});

// ===== MODE SOMBRE =====
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});

// ===== ASSISTANT IA — COMMENTAIRE =====
function aiComment(action, btn) {
    var textarea = document.getElementById('comment-contenu');
    var texte = textarea.value.trim();

    if (!texte || texte.length < 5) {
        alert('Écris d\'abord un commentaire (min 5 caractères).');
        return;
    }

    // Spinner sur le bouton
    var originalHtml = btn.innerHTML;
    btn.classList.add('loading');
    btn.innerHTML = '<span class="ai-spinner"></span> ...';

    // Cacher suggestion précédente
    document.getElementById('ai-comment-suggestion').style.display = 'none';

    var formData = new FormData();
    formData.append('action', action);
    formData.append('texte', texte);

    fetch('ai_writing.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.classList.remove('loading');
        btn.innerHTML = originalHtml;

        if (data.success) {
            document.getElementById('ai-comment-suggestion-text').innerText = data.result;
            var box = document.getElementById('ai-comment-suggestion');
            box.style.display = 'block';
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert('IA : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(function(err) {
        btn.classList.remove('loading');
        btn.innerHTML = originalHtml;
        alert('Erreur réseau. Vérifie ta connexion.');
        console.error(err);
    });
}

function acceptCommentSuggestion() {
    var textarea = document.getElementById('comment-contenu');
    var suggText = document.getElementById('ai-comment-suggestion-text').innerText;
    textarea.value = suggText;
    document.getElementById('ai-comment-suggestion').style.display = 'none';
    // Flash vert pour confirmer
    textarea.style.borderColor = '#27ae60';
    setTimeout(function() { textarea.style.borderColor = ''; }, 1200);
}
</script>

</body>
</html>