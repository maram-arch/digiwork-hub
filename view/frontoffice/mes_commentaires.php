<style id="theme-style">
    body { background: #fff; color: #000; transition: 0.3s; }
    body.dark-mode { background: #1e1e2f; color: #eee; }
    body.dark-mode .card, body.dark-mode .pub-card, body.dark-mode .form-container { background: #2a2a3a; color: #eee; border-color: #444; }
</style>
<button id="theme-toggle" class="btn btn-sm btn-secondary position-fixed bottom-0 end-0 m-3">🌓 Thème</button>
<script>
document.getElementById('theme-toggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
</script>
<?php


session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CommentaireController.php';
require_once __DIR__ . '/../../controller/PublicationController.php';

// Récupérer l'utilisateur connecté (à adapter)
$dbTemp  = Config::getConnexion();
$id_user = (int)$dbTemp->query(
    "SELECT id_user FROM user ORDER BY id_user ASC LIMIT 1"
)->fetchColumn();
if ($id_user <= 0) $id_user = 1; // fallback

$commentaireController = new CommentaireController();
// Récupérer les commentaires de l'utilisateur avec jointure publication
$commentaires = $commentaireController->getCommentairesByUser($id_user);

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commentaires - DigiWork Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Mêmes styles que candidatures.php, adaptés pour commentaires */
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
 
        :root {
            --brand:     #435ebe;
            --brand-lt:  #eef1fb;
            --brand-dk:  #2f44a0;
            --success:   #10b981;
            --success-lt:#ecfdf5;
            --danger:    #ef4444;
            --danger-lt: #fff1f1;
            --warn:      #f59e0b;
            --warn-lt:   #fffbeb;
            --bg:        #f0f2f9;
            --surface:   #ffffff;
            --text:      #1e2535;
            --muted:     #7c8db0;
            --border:    #e8ecf6;
            --radius:    16px;
            --shadow:    0 4px 24px rgba(67,94,190,.10);
            --shadow-hov:0 8px 36px rgba(67,94,190,.18);
        }
 
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
 
        .navbar { background:var(--surface); padding:0 36px; height:64px; display:flex; align-items:center; gap:24px; justify-content:space-between; box-shadow:0 1px 0 var(--border); position:sticky; top:0; z-index:100; }
        .navbar-left { display:flex; align-items:center; gap:24px; }
        .navbar-right { display:flex; align-items:center; gap:24px; }
        .navbar img { height:38px; flex-shrink:0; }
        .nav-links { display:flex; gap:4px; align-items:center; }
        .nav-link { color:var(--muted); text-decoration:none; font-size:14px; font-weight:500; padding:8px 14px; border-radius:10px; transition:all .2s; }
        .nav-link:hover { background:var(--brand-lt); color:var(--brand); }
        .nav-link.active { background:var(--brand-lt); color:var(--brand); font-weight:700; }
        .btn-forum { background:var(--brand); color:#fff; text-decoration:none; font-size:13px; font-weight:700; padding:9px 20px; border-radius:10px; transition:background .2s; flex-shrink:0; }
        .btn-forum:hover { background:var(--brand-dk); }
 
        .container { max-width:1200px; margin:0 auto; padding:40px 24px; }
        .page-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px; }
        .page-header h1 { font-size:28px; font-weight:800; letter-spacing:-.5px; margin-bottom:4px; }
        .page-header p  { font-size:14px; color:var(--muted); }
        .count-pill { background:var(--brand-lt); color:var(--brand); font-size:13px; font-weight:700; padding:6px 16px; border-radius:20px; }
 
        .alert { padding:14px 18px; border-radius:12px; margin-bottom:28px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
        .alert-success { background:var(--success-lt); color:#065f46; border-left:4px solid var(--success); }
        .alert-danger  { background:var(--danger-lt);  color:#991b1b; border-left:4px solid var(--danger); }
 
        .cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:20px; }
 
        .comment-card {
            background:var(--surface);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            border:1px solid var(--border);
            overflow:hidden;
            display:flex;
            flex-direction:column;
            transition:transform .2s,box-shadow .2s;
            animation:fadeUp .35s ease both;
        }
        .comment-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-hov); }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .comment-card:nth-child(1){animation-delay:.05s}
        .comment-card:nth-child(2){animation-delay:.10s}
        .comment-card:nth-child(3){animation-delay:.15s}
        .comment-card:nth-child(4){animation-delay:.20s}
 
        .card-head { padding:20px 20px 14px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        .pub-info { flex:1; min-width:0; }
        .pub-titre { font-size:16px; font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pub-cat  { display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:var(--brand-lt); color:var(--brand); margin-bottom:6px; }
        .comment-date { font-size:12px; color:var(--muted); margin-top:4px; display:flex; align-items:center; gap:4px; }
 
        .card-body { padding:0 20px 16px; flex:1; display:flex; flex-direction:column; gap:12px; }
        .divider { height:1px; background:var(--border); margin:0 -20px; }
        .comment-content { background:#f8faff; border-radius:12px; padding:12px 14px; font-size:13px; line-height:1.6; color:var(--text); border-left:3px solid var(--brand-lt); }
 
        .card-foot { padding:14px 20px; border-top:1px solid var(--border); background:#fafbff; display:flex; gap:8px; justify-content:flex-end; align-items:center; }
        .btn-a { display:inline-flex; align-items:center; gap:6px; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
        .btn-edit { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .btn-edit:hover { background:#ffedd5; }
        .btn-del  { background:var(--danger-lt); color:var(--danger); border:1px solid #fecaca; }
        .btn-del:hover { background:#ffe4e4; }
 
        .empty-wrap { grid-column:1/-1; text-align:center; padding:80px 24px; background:var(--surface); border-radius:var(--radius); border:2px dashed var(--border); }
        .empty-ic { width:80px; height:80px; background:var(--brand-lt); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px; }
        .empty-wrap h3 { font-size:20px; font-weight:800; margin-bottom:8px; }
        .empty-wrap p  { color:var(--muted); font-size:14px; margin-bottom:24px; }
        .btn-voir { display:inline-block; background:var(--brand); color:#fff; text-decoration:none; padding:12px 28px; border-radius:10px; font-weight:700; font-size:14px; transition:background .2s; }
        .btn-voir:hover { background:var(--brand-dk); }
 
        /* MODALES */
        .modal-ov { display:none; position:fixed; inset:0; background:rgba(15,20,50,.55); backdrop-filter:blur(3px); z-index:9999; justify-content:center; align-items:center; padding:16px; }
        .modal-ov.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:520px; max-height:92vh; overflow-y:auto; box-shadow:0 32px 80px rgba(0,0,0,.28); animation:mIn .22s cubic-bezier(.34,1.56,.64,1); }
        @keyframes mIn { from{transform:translateY(-24px) scale(.97);opacity:0} to{transform:translateY(0) scale(1);opacity:1} }
        .modal-top { display:flex; justify-content:space-between; align-items:center; padding:20px 24px 16px; border-bottom:1px solid var(--border); }
        .modal-top h5 { font-size:16px; font-weight:700; }
        .btn-x { background:#f1f3f9; border:none; border-radius:8px; width:32px; height:32px; font-size:16px; cursor:pointer; color:var(--muted); display:flex; align-items:center; justify-content:center; transition:all .15s; }
        .btn-x:hover { background:var(--danger-lt); color:var(--danger); }
        .modal-body { padding:22px 24px; }
        .modal-foot { padding:16px 24px; border-top:1px solid var(--border); background:#f8f9ff; border-radius:0 0 20px 20px; display:flex; justify-content:flex-end; gap:10px; }
        .field-lbl { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; display:block; }
        .m-in { font-size:14px; font-family:inherit; border-radius:10px; border:1.5px solid var(--border); width:100%; padding:10px 14px; resize:vertical; transition:border-color .2s; background:#fafbff; }
        .m-in:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .char-c { font-size:12px; color:var(--muted); margin-top:4px; }
        .err { font-size:12px; color:var(--danger); margin-top:4px; display:none; }
        .err.show { display:block; }
        .btn-cancel { background:#f1f3f9; border:none; color:var(--muted); border-radius:10px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }
        .btn-cancel:hover { background:var(--border); }
        .btn-save { background:var(--brand); border:none; color:#fff; border-radius:10px; padding:10px 24px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; }
        .btn-save:hover { background:var(--brand-dk); }
        .del-ic { width:68px; height:68px; background:var(--danger-lt); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:28px; }
        .btn-del-c { background:var(--danger); color:#fff; border:none; border-radius:10px; padding:10px 28px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .btn-del-c:hover { background:#dc2626; }
 
        @media(max-width:640px){
            .container{padding:24px 16px;}
            .cards-grid{grid-template-columns:1fr;}
            .navbar{padding:0 16px;}
            .nav-links{display:none;}
            .page-header{flex-direction:column;align-items:flex-start;}
        }
    </style>
</head>
<!-- Chatbot Widget -->
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
/* Mode sombre */
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
            <div class="message bot-message">Bonjour ! Posez-moi une question sur les offres, le forum, les candidatures...</div>
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

        fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg)
        })
        .then(res => res.json())
        .then(data => {
            addMessage('bot', data.reply);
        })
        .catch(err => {
            addMessage('bot', 'Erreur de connexion. Réessayez.');
            console.error(err);
        });
    }

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);
    if (input) input.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
})();
</script>
<body>
 
<nav class="navbar">
    <div class="navbar-left">
        <img src="assets/img/logo/logo.png" style="width:230px;">
        <a href="publications.php" class="btn-forum">+ Nouveau commentaire</a>
    </div>
    <div class="navbar-right">
        <div class="nav-links">
            <a href="index.php"  class="nav-link">Accueil</a>
            <a href="publications.php" class="nav-link">Forum</a>
            <a href="mes_commentaires.php" class="nav-link active">Mes commentaires</a>
        </div>
    </div>
</nav>
 
<div class="container">
 
    <div class="page-header">
        <div>
            <h1>Mes commentaires</h1>
            <p>Retrouvez tous vos échanges sur le forum</p>
        </div>
        <span class="count-pill"><?= count($commentaires) ?> commentaire<?= count($commentaires) > 1 ? 's' : '' ?></span>
    </div>
 
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
    </div>
    <?php endif; ?>
 
    <div class="cards-grid">
 
    <?php if (count($commentaires) > 0): ?>
        <?php foreach ($commentaires as $c): ?>
        <div class="comment-card">
            <div class="card-head">
                <div class="pub-info">
                    <div class="pub-cat"><?= htmlspecialchars($c['categorie'] ?? 'général') ?></div>
                    <div class="pub-titre" title="<?= htmlspecialchars($c['titre_publication'] ?? '') ?>">
                        <?= htmlspecialchars($c['titre_publication'] ?? '(sans titre)') ?>
                    </div>
                    <div class="comment-date">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Posté le <?= date('d/m/Y à H:i', strtotime($c['date_commentaire'])) ?>
                    </div>
                </div>
            </div>
 
            <div class="card-body">
                <div class="divider"></div>
                <div class="comment-content">
                    <?= nl2br(htmlspecialchars($c['contenu'])) ?>
                </div>
            </div>
 
            <div class="card-foot">
                <button class="btn-a btn-edit"
                    data-id_commentaire="<?= (int)$c['id_commentaire'] ?>"
                    data-contenu="<?= htmlspecialchars($c['contenu'], ENT_QUOTES) ?>"
                    onclick="openEditModal(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modifier
                </button>
                <button class="btn-a btn-del"
                    data-id_commentaire="<?= (int)$c['id_commentaire'] ?>"
                    data-titre="<?= htmlspecialchars(substr($c['contenu'], 0, 50), ENT_QUOTES) ?>"
                    onclick="openDeleteModal(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    Supprimer
                </button>
            </div>
        </div>
        <?php endforeach; ?>
 
    <?php else: ?>
        <div class="empty-wrap">
            <div class="empty-ic">💬</div>
            <h3>Aucun commentaire</h3>
            <p>Vous n'avez encore rien posté sur le forum.</p>
            <a href="publications.php" class="btn-voir">Participer au forum</a>
        </div>
    <?php endif; ?>
 
    </div>
</div>
 
<!-- MODALE MODIFIER COMMENTAIRE -->
<div class="modal-ov" id="editModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>✏️ Modifier mon commentaire</h5>
            <button class="btn-x" onclick="closeEditModal()">✕</button>
        </div>
        <form method="POST" action="modifier_commentaire.php" id="editForm" onsubmit="return validateEdit(event)">
            <input type="hidden" name="id_commentaire" id="edit_id_commentaire">
            <div class="modal-body">
                <div>
                    <label class="field-lbl">📝 Contenu du commentaire *</label>
                    <textarea class="m-in" name="contenu" id="edit_contenu" rows="6"
                              placeholder="Votre commentaire..." maxlength="1000"></textarea>
                    <div class="char-c" id="edit_count">0 / 1000 caractères</div>
                    <div class="err" id="err_contenu"></div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>
 
<!-- MODALE SUPPRIMER -->
<div class="modal-ov" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>Supprimer le commentaire</h5>
            <button class="btn-x" onclick="closeDeleteModal()">✕</button>
        </div>
        <div class="modal-body" style="text-align:center;padding:32px 24px">
            <div class="del-ic">🗑️</div>
            <h6 style="font-size:17px;font-weight:800;margin-bottom:8px">Êtes-vous sûr ?</h6>
            <p style="color:var(--muted);font-size:14px;margin-bottom:6px">Vous allez supprimer ce commentaire :</p>
            <p id="delete-titre" style="font-weight:700;color:var(--danger);font-size:15px;margin-bottom:6px"></p>
            <p style="color:var(--muted);font-size:12px">Cette action est irréversible.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button class="btn-cancel" onclick="closeDeleteModal()" style="padding:10px 28px">Annuler</button>
            <a id="delete-confirm" href="#" class="btn-del-c">🗑️ Oui, supprimer</a>
        </div>
    </div>
</div>
 
<script>
function openEditModal(btn) {
    document.getElementById('edit_id_commentaire').value = btn.dataset.id_commentaire;
    let contenu = btn.dataset.contenu || '';
    document.getElementById('edit_contenu').value = contenu;
    let len = contenu.length;
    document.getElementById('edit_count').textContent = len + ' / 1000 caractères';
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}
// Compteur en temps réel
const editTextarea = document.getElementById('edit_contenu');
if (editTextarea) {
    editTextarea.addEventListener('input', function() {
        let len = this.value.length;
        document.getElementById('edit_count').textContent = len + ' / 1000 caractères';
    });
}
function validateEdit(e) {
    e.preventDefault();
    let contenu = document.getElementById('edit_contenu').value.trim();
    let errDiv = document.getElementById('err_contenu');
    if (contenu.length < 2) {
        errDiv.textContent = 'Le commentaire doit contenir au moins 2 caractères.';
        errDiv.classList.add('show');
        return false;
    }
    if (/<[^>]*>/g.test(contenu)) {
        errDiv.textContent = 'Les balises HTML ne sont pas autorisées.';
        errDiv.classList.add('show');
        return false;
    }
    errDiv.classList.remove('show');
    document.getElementById('editForm').submit();
    return false;
}
function openDeleteModal(btn) {
    document.getElementById('delete-titre').textContent = btn.dataset.titre;
    document.getElementById('delete-confirm').href = 'supprimer_commentaire.php?id_commentaire=' + btn.dataset.id_commentaire;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('editModal').addEventListener('click', function(e){ if(e.target===this) closeEditModal(); });
document.getElementById('deleteModal').addEventListener('click', function(e){ if(e.target===this) closeDeleteModal(); });
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeEditModal(); closeDeleteModal(); } });
</script>
</body>
</html>