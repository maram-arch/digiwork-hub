<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;   // Remplacez par un ID valide
}

require_once __DIR__ . '/../../model/Publication.php';
require_once __DIR__ . '/../../config/config.php';
session_start();

// ========== AJAX LIKES & FAVORIS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $id_user = $_SESSION['id_user'] ?? 0;
    if (!$id_user) { echo json_encode(['success' => false]); exit; }
    $id_pub = (int)($_POST['id_publication'] ?? 0);
    if ($_GET['action'] === 'like') {
        if (!$id_pub) { echo json_encode(['success' => false]); exit; }
        $result = Publication::toggleLike($id_pub, $id_user);
        echo json_encode(['success' => true, 'action' => $result['action'], 'nb_likes' => $result['nb_likes']]);
        exit;
    } elseif ($_GET['action'] === 'favori') {
        if (!$id_pub) { echo json_encode(['success' => false]); exit; }
        if (Publication::isFavori($id_pub, $id_user)) {
            Publication::removeFavori($id_pub, $id_user);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            Publication::addFavori($id_pub, $id_user);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }

}

// Récupération des paramètres
$categorie = $_GET['categorie'] ?? 'all';
$tri = $_GET['tri'] ?? 'date';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;

$publications = Publication::getAllWithFilters($categorie, $tri, $page, $perPage);
$total = Publication::countWithFilters($categorie);
$totalPages = ceil($total / $perPage);

$likedIds = [];
$favoriIds = [];
if (isset($_SESSION['id_user'])) {
    foreach ($publications as $p) {
        if (Publication::hasLiked($p['id_publication'], $_SESSION['id_user'])) $likedIds[] = $p['id_publication'];
        if (Publication::isFavori($p['id_publication'], $_SESSION['id_user'])) $favoriIds[] = $p['id_publication'];
    }
}

function tronquer($txt, $len=120) { 
    return strlen($txt) <= $len ? $txt : substr($txt,0,strrpos(substr($txt,0,$len),' ')).'…'; 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <style>
        body { background: #f0f2f9; transition: 0.3s; }
        body.dark-mode { background: #1e1e2f; color: #f0f0f0; }
        body.dark-mode .pub-card, body.dark-mode .filter-form, body.dark-mode .navbar { background: #2a2a3a; color: #f0f0f0; border-color: #444; }
        body.dark-mode .pub-card * { color: #f0f0f0; }
        body.dark-mode .text-muted, body.dark-mode small { color: #bbbbbb !important; }
        body.dark-mode .btn-outline-secondary { color: #fff; border-color: #777; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #3a3a4a; color: #fff; border-color: #555; }
        .pub-card { background: #fff; border-radius: 20px; border: 1px solid #e8eaf0; padding: 24px; height: 100%; transition: 0.2s; }
        .like-btn, .favori-btn { background: none; border: none; cursor: pointer; color: #6c757d; }
        .like-btn.liked { color: #e74c3c; }
        .favori-btn.active { color: #f1c40f; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: #435ebe; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; z-index: 1000; }
    </style>
</head>

<body>
<header class="header header-6">
    <div class="navbar-area">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.php"><img src="assets/img/logo/logo.png" style="width:250px;"></a>
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

<section class="hero" style="background:linear-gradient(135deg,#435ebe 0%,#263587 100%);padding:50px 0;color:white;margin-bottom:40px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8"><h1>Forum DigiWork Hub</h1><p>Partagez vos idées, posez vos questions.</p></div>
            <div class="col-lg-4 text-lg-end"><div class="bg-white text-dark rounded p-3 d-inline-block"><div style="font-size:30px;font-weight:800;"><?= $total ?></div><div>publication<?= $total>1?'s':'' ?></div></div></div>
        </div>
    </div>
</section>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2>📢 Dernières publications</h2><a href="ajouter_publication.php" class="btn btn-success">➕ Nouvelle publication</a></div>
    <div class="filter-form bg-white p-3 rounded mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4"><select name="categorie" class="form-select"><option value="all" <?= $categorie=='all'?'selected':'' ?>>Toutes catégories</option><option value="general" <?= $categorie=='general'?'selected':'' ?>>Général</option><option value="stage" <?= $categorie=='stage'?'selected':'' ?>>Stage</option><option value="job" <?= $categorie=='job'?'selected':'' ?>>Job</option><option value="question" <?= $categorie=='question'?'selected':'' ?>>Question</option><option value="evenement" <?= $categorie=='evenement'?'selected':'' ?>>Événement</option></select></div>
            <div class="col-md-4"><select name="tri" class="form-select"><option value="date" <?= $tri=='date'?'selected':'' ?>>Date récente</option><option value="likes" <?= $tri=='likes'?'selected':'' ?>>Plus de likes</option><option value="vues" <?= $tri=='vues'?'selected':'' ?>>Plus de vues</option></select></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">Appliquer</button></div>
        </form>
    </div>

    <?php if (!empty($publications)): ?>
        <div class="row g-4">
            <?php foreach ($publications as $pub): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="pub-card">
                        <?php if ($pub['image']): ?><img src="../../<?= htmlspecialchars($pub['image']) ?>" class="w-100 rounded mb-2" style="max-height:150px;object-fit:cover;"><?php endif; ?>
                        <div><span class="badge bg-primary"><?= htmlspecialchars($pub['categorie']) ?></span><?php if ($pub['is_event']): ?><span class="badge bg-warning ms-1">🎉 Événement</span><?php endif; ?></div>
                        <h5 class="mt-2"><?= htmlspecialchars($pub['titre']) ?></h5>
                        <p class="small text-muted"><?= htmlspecialchars($pub['prenom']??'').' '.htmlspecialchars($pub['nom']??'') ?> - <?= date('d/m/Y',strtotime($pub['date_publication'])) ?></p>
                        <p><?= nl2br(htmlspecialchars(tronquer($pub['contenu'],100))) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div><button class="like-btn <?= in_array($pub['id_publication'],$likedIds)?'liked':'' ?>" data-id="<?= $pub['id_publication'] ?>">❤️ <span class="like-count"><?= (int)$pub['nb_likes'] ?></span></button>
                            <button class="favori-btn ms-2 <?= in_array($pub['id_publication'],$favoriIds)?'active':'' ?>" data-id="<?= $pub['id_publication'] ?>">⭐</button></div>
                            <span>👁️ <?= (int)$pub['nb_vues'] ?></span>
                        </div>
                        <div class="mt-3"><a href="detail_publication.php?id=<?= $pub['id_publication'] ?>" class="btn btn-sm btn-outline-primary w-100">Lire la suite</a></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav><ul class="pagination justify-content-center"><?php for($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?categorie=<?= urlencode($categorie) ?>&tri=<?= urlencode($tri) ?>&page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">Aucune publication trouvée.</div>
    <?php endif; ?>
</div>

<button id="themeToggle" class="theme-toggle">🌓</button>
<script>
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        fetch('publications.php?action=like', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id_publication='+id })
        .then(r=>r.json()).then(d=>{ if(d.success){ this.querySelector('.like-count').innerText=d.nb_likes; if(d.action==='liked') this.classList.add('liked'); else this.classList.remove('liked'); } });
    });
});
document.querySelectorAll('.favori-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        fetch('publications.php?action=favori', { method:'POST', body:'id_publication='+id }).then(r=>r.json()).then(d=>{ if(d.success){ if(d.action==='added') this.classList.add('active'); else this.classList.remove('active'); } });
    });
});
if (localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', ()=>{ document.body.classList.toggle('dark-mode'); localStorage.setItem('theme', document.body.classList.contains('dark-mode')?'dark':'light'); });
</script>
<!-- Widget Chatbot DigiWork Hub -->
<style>
.chatbot-widget {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1050;
}
.chatbot-toggle {
    background: #435ebe;
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 8px;
}
.chatbot-window {
    display: none;
    position: absolute;
    bottom: 70px;
    left: 0;
    width: 320px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    overflow: hidden;
    flex-direction: column;
    font-family: 'Segoe UI', sans-serif;
}
.chatbot-header {
    background: #435ebe;
    color: white;
    padding: 12px;
    font-weight: bold;
}
.chatbot-messages {
    height: 300px;
    overflow-y: auto;
    padding: 10px;
    background: #f9f9f9;
    font-size: 13px;
}
.message {
    margin-bottom: 10px;
}
.user-message {
    text-align: right;
    color: #2c3e50;
}
.bot-message {
    text-align: left;
    color: #435ebe;
}
.chatbot-input {
    display: flex;
    border-top: 1px solid #ddd;
}
.chatbot-input input {
    flex: 1;
    border: none;
    padding: 10px;
    outline: none;
}
.chatbot-input button {
    background: #435ebe;
    color: white;
    border: none;
    padding: 0 15px;
    cursor: pointer;
}
.dark-mode .chatbot-window {
    background: #2a2a3a;
    color: #eee;
}
.dark-mode .chatbot-messages {
    background: #1e1e2f;
}
.dark-mode .user-message {
    color: #ccc;
}
.dark-mode .bot-message {
    color: #a0c0ff;
}
.dark-mode .chatbot-input input {
    background: #3a3a4a;
    color: #fff;
}
</style>

<div class="chatbot-widget">
    <button class="chatbot-toggle">💬 DigiBot</button>
    <div class="chatbot-window">
        <div class="chatbot-header">🤖 Assistant DigiWork Hub</div>
        <div class="chatbot-messages" id="chatMessages">
            <div class="message bot-message">Bonjour ! Posez une question sur le forum, les offres ou les candidatures.</div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatInput" placeholder="Votre question...">
            <button id="chatSend">Envoyer</button>
        </div>
    </div>
</div>

<script>
(function() {
    const toggleBtn = document.querySelector('.chatbot-toggle');
    const chatWindow = document.querySelector('.chatbot-window');
    const messagesDiv = document.getElementById('chatMessages');
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSend');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
        });
    }

    function addMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message ' + (sender === 'user' ? 'user-message' : 'bot-message');
        msgDiv.innerHTML = (sender === 'user' ? '🧑 ' : '🤖 ') + text.replace(/\n/g, '<br>');
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function sendMessage() {
        const msg = input.value.trim();
        if (!msg) return;
        addMessage('user', msg);
        input.value = '';

        fetch('chatbot_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg)
        })
        .then(res => res.json())
        .then(data => {
            addMessage('bot', data.reply);
        })
        .catch(err => {
            addMessage('bot', '⚠️ Erreur de connexion. Réessayez.');
            console.error(err);
        });
    }

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);
    if (input) input.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
})();
</script>
</body>
</html>