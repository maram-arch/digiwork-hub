<?php
// Récupérer le flash message
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$id_user_session = $_SESSION['id_user'] ?? null;
$search_val  = htmlspecialchars($_GET['search']    ?? '');
$cat_val     = htmlspecialchars($_GET['categorie'] ?? '');
$tri_val     = htmlspecialchars($_GET['tri']       ?? 'date');
$page_val    = max(1, (int)($_GET['page'] ?? 1));

$cat_labels = [
    'general'   => ['label' => 'Général',    'icon' => '💬', 'color' => '#6B7280'],
    'stage'     => ['label' => 'Stage',      'icon' => '🎓', 'color' => '#8B5CF6'],
    'job'       => ['label' => 'Emploi',     'icon' => '💼', 'color' => '#059669'],
    'question'  => ['label' => 'Question',   'icon' => '❓', 'color' => '#F59E0B'],
    'evenement' => ['label' => 'Événement',  'icon' => '📅', 'color' => '#EF4444'],
];
?>
<style>
        :root {
            --primary: #2F80ED;
            --primary-dark: #1a6fd4;
            --danger: #EF4444;
            --success: #10B981;
            --bg: #F0F4F8;
            --card-bg: #ffffff;
            --text: #1E293B;
            --muted: #64748B;
            --border: #E2E8F0;
        }
        * { box-sizing: border-box; }
        body { background: var(--bg); font-family: 'Heebo', sans-serif; margin: 0; }

        /* ── Header ── */
        .page-header {
            background: #fff;
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        }
        .page-header h1 { font-size: 20px; font-weight: 700; color: var(--text); margin: 0; font-family: 'Fira Sans', sans-serif; }
        .badge-total { background: rgba(47,128,237,0.12); color: var(--primary); font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-left: 8px; }
        .btn-new { background: var(--primary); color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background .2s; }
        .btn-new:hover { background: var(--primary-dark); color: #fff; text-decoration: none; }

        /* ── Flash ── */
        .flash { padding: 12px 20px; border-radius: 8px; margin: 20px 32px 0; font-size: 14px; font-weight: 500; }
        .flash-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #065F46; }
        .flash-error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: #7F1D1D; }

        /* ── Toolbar ── */
        .toolbar { padding: 20px 32px 0; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .search-wrap { position: relative; flex: 1; min-width: 220px; max-width: 380px; }
        .search-wrap input {
            width: 100%; padding: 10px 14px 10px 38px;
            border: 1px solid var(--border); border-radius: 8px;
            font-size: 14px; font-family: 'Heebo', sans-serif;
            background: #fff; color: var(--text);
            transition: border-color .2s;
        }
        .search-wrap input:focus { border-color: var(--primary); outline: none; }
        .search-wrap .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 15px; }
        .toolbar select {
            padding: 9px 14px; border: 1px solid var(--border); border-radius: 8px;
            font-size: 13px; font-family: 'Heebo', sans-serif;
            background: #fff; color: var(--text); cursor: pointer;
            transition: border-color .2s;
        }
        .toolbar select:focus { border-color: var(--primary); outline: none; }
        .btn-search { background: var(--primary); color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'Heebo', sans-serif; transition: background .2s; }
        .btn-search:hover { background: var(--primary-dark); }
        .btn-reset { color: var(--muted); font-size: 13px; text-decoration: none; padding: 10px 4px; }
        .btn-reset:hover { color: var(--primary); }

        /* ── Cat filter pills ── */
        .cat-pills { padding: 14px 32px 0; display: flex; gap: 8px; flex-wrap: wrap; }
        .cat-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 500;
            text-decoration: none; border: 1.5px solid var(--border);
            background: #fff; color: var(--muted);
            transition: all .2s;
        }
        .cat-pill:hover, .cat-pill.active { border-color: var(--primary); color: var(--primary); background: rgba(47,128,237,0.07); }

        /* ── Grid ── */
        .wrapper { padding: 20px 32px 40px; }
        .pub-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px; }

        /* ── Card ── */
        .pub-card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: box-shadow .2s, transform .15s;
            display: flex; flex-direction: column;
        }
        .pub-card:hover { box-shadow: 0 8px 28px rgba(47,128,237,0.12); transform: translateY(-2px); }
        .card-img { width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid var(--border); }
        .card-img-placeholder { width: 100%; height: 8px; background: var(--cat-color, var(--primary)); }
        .card-body { padding: 18px 20px; flex: 1; display: flex; flex-direction: column; }
        .card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 8px; flex-wrap: wrap; }
        .cat-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
            padding: 3px 10px; border-radius: 20px;
            background: var(--cat-bg); color: var(--cat-color);
        }
        .card-date { font-size: 12px; color: var(--muted); }
        .card-title { font-family: 'Fira Sans', sans-serif; font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 8px; line-height: 1.4; }
        .card-title a { color: inherit; text-decoration: none; }
        .card-title a:hover { color: var(--primary); }
        .card-content { font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 12px; flex: 1; }
        .event-box { background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 12px; color: #B91C1C; }
        .event-box strong { display: block; font-size: 13px; margin-bottom: 2px; }

        /* ── Card footer ── */
        .card-footer-row {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 12px; border-top: 1px solid var(--border);
            gap: 8px; flex-wrap: wrap;
        }
        .meta-left { display: flex; align-items: center; gap: 12px; }
        .meta-stat { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--muted); }
        .btn-like {
            background: none; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 4px;
            font-size: 12px; color: var(--muted);
            padding: 4px 8px; border-radius: 6px;
            transition: all .15s; font-family: 'Heebo', sans-serif;
        }
        .btn-like:hover { background: rgba(239,68,68,0.08); color: #EF4444; }
        .btn-like.liked { color: #EF4444; }
        .btn-like.liked .heart { filter: none; }
        .heart { font-size: 14px; }
        .author-chip { font-size: 12px; color: var(--muted); background: #F8FAFC; border: 1px solid var(--border); border-radius: 20px; padding: 2px 10px; display: inline-flex; align-items: center; gap: 4px; }
        .actions { display: flex; gap: 6px; }
        .btn-edit-sm, .btn-del-sm {
            font-size: 12px; padding: 4px 12px; border-radius: 6px;
            text-decoration: none; border: 1.5px solid; transition: all .2s;
            white-space: nowrap;
        }
        .btn-edit-sm { color: var(--primary); border-color: var(--primary); }
        .btn-edit-sm:hover { background: var(--primary); color: #fff; text-decoration: none; }
        .btn-del-sm { color: var(--danger); border-color: var(--danger); }
        .btn-del-sm:hover { background: var(--danger); color: #fff; text-decoration: none; }

        /* ── Bouton commenter ── */
        .btn-comment {
            background: none; border: 1.5px solid var(--border);
            color: var(--muted); font-size: 12px; font-weight: 600;
            padding: 5px 12px; border-radius: 6px; cursor: pointer;
            font-family: 'Heebo', sans-serif;
            display: inline-flex; align-items: center; gap: 5px;
            transition: all .2s;
        }
        .btn-comment:hover, .btn-comment.open {
            border-color: var(--primary); color: var(--primary);
            background: rgba(47,128,237,0.06);
        }

        /* ── Panneau commentaire inline ── */
        .comment-panel {
            display: none;
            padding: 14px 20px 16px;
            border-top: 1px solid var(--border);
            background: #F8FAFC;
            animation: slideDown .18s ease;
        }
        .comment-panel.open { display: block; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .comment-panel textarea {
            width: 100%; padding: 10px 12px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; font-family: 'Heebo', sans-serif;
            resize: vertical; min-height: 72px;
            transition: border-color .2s; box-sizing: border-box;
            background: #fff; color: var(--text);
        }
        .comment-panel textarea:focus { border-color: var(--primary); outline: none; }
        .comment-panel textarea.error { border-color: var(--danger); }
        .comment-panel .panel-footer {
            display: flex; align-items: center;
            justify-content: space-between; margin-top: 8px; gap: 8px;
        }
        .comment-panel .char-info { font-size: 11px; color: var(--muted); }
        .comment-panel .char-info.warn { color: var(--danger); font-weight: 600; }
        .comment-panel .err-msg {
            font-size: 12px; color: var(--danger);
            margin-top: 4px; display: none;
        }
        .comment-panel .btn-send {
            background: var(--primary); color: #fff; border: none;
            padding: 8px 18px; border-radius: 7px; font-size: 13px;
            font-weight: 600; cursor: pointer; font-family: 'Heebo', sans-serif;
            transition: background .2s; white-space: nowrap;
        }
        .comment-panel .btn-send:hover { background: var(--primary-dark); }
        .comment-panel .btn-cancel {
            background: none; border: none; color: var(--muted);
            font-size: 12px; cursor: pointer; font-family: 'Heebo', sans-serif;
            padding: 4px 6px; border-radius: 5px;
        }
        .comment-panel .btn-cancel:hover { color: var(--danger); }

        /* ── Pagination ── */
        .pagination-wrap { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 32px; flex-wrap: wrap; }
        .page-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 10px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; font-weight: 500; text-decoration: none; color: var(--text);
            background: #fff; transition: all .2s;
        }
        .page-btn:hover { border-color: var(--primary); color: var(--primary); text-decoration: none; }
        .page-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .page-btn.disabled { pointer-events: none; opacity: .45; }

        /* ── Empty ── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 16px; }

        @media (max-width: 600px) {
            .toolbar { flex-direction: column; }
            .wrapper { padding: 16px; }
            .page-header { padding: 12px 16px; }
            .cat-pills, .flash { padding-left: 16px; padding-right: 16px; }
        }
</style>

<!-- Header -->
<div class="page-header">
    <div style="display:flex;align-items:center;gap:8px;">
        <h1>Forum DigiWork</h1>
        <span class="badge-total"><?php echo $total; ?> pub.</span>
    </div>
    <a href="index.php?action=addPublication" class="btn-new">+ Nouvelle publication</a>
</div>

<!-- Flash messages -->
<?php if ($flash_success): ?>
    <div class="flash flash-success"><?php echo htmlspecialchars($flash_success); ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
    <div class="flash flash-error"><?php echo htmlspecialchars($flash_error); ?></div>
<?php endif; ?>

<!-- Catégories pills -->
<div class="cat-pills">
    <a href="index.php?action=list&search=<?php echo urlencode($search_val); ?>&tri=<?php echo $tri_val; ?>"
       class="cat-pill <?php echo $cat_val === '' ? 'active' : ''; ?>">🌐 Tous</a>
    <?php foreach ($cat_labels as $key => $cat): ?>
        <a href="index.php?action=list&search=<?php echo urlencode($search_val); ?>&categorie=<?php echo $key; ?>&tri=<?php echo $tri_val; ?>"
           class="cat-pill <?php echo $cat_val === $key ? 'active' : ''; ?>">
            <?php echo $cat['icon']; ?> <?php echo $cat['label']; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Toolbar -->
<form method="GET" action="index.php">
    <input type="hidden" name="action" value="list">
    <?php if ($cat_val): ?><input type="hidden" name="categorie" value="<?php echo $cat_val; ?>"><?php endif; ?>
    <div class="toolbar">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" value="<?php echo $search_val; ?>" placeholder="Rechercher titre ou contenu…">
        </div>
        <select name="tri">
            <option value="date"     <?php echo $tri_val === 'date'     ? 'selected' : ''; ?>>📅 Plus récents</option>
            <option value="date_asc" <?php echo $tri_val === 'date_asc' ? 'selected' : ''; ?>>📅 Plus anciens</option>
            <option value="likes"    <?php echo $tri_val === 'likes'    ? 'selected' : ''; ?>>❤️ Popularité</option>
            <option value="vues"     <?php echo $tri_val === 'vues'     ? 'selected' : ''; ?>>👁️ Plus vus</option>
        </select>
        <button type="submit" class="btn-search">Filtrer</button>
        <?php if ($search_val || $cat_val): ?>
            <a href="index.php?action=list" class="btn-reset">✕ Réinitialiser</a>
        <?php endif; ?>
    </div>
</form>

<!-- Liste -->
<div class="wrapper">
    <?php if (empty($publications)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>Aucune publication trouvée.</p>
            <br>
            <a href="index.php?action=add" class="btn-new">+ Créer une publication</a>
        </div>
    <?php else: ?>
        <div class="pub-grid">
            <?php foreach ($publications as $pub):
                $cat  = $cat_labels[$pub['categorie']] ?? $cat_labels['general'];
                $isOwner = $id_user_session && ($id_user_session == $pub['id_user']);
                $hasLiked = in_array($pub['id_publication'], $liked_ids ?? []);
                $auteur = trim($pub['auteur_nom']) ?: $pub['auteur_email'] ?: '#' . $pub['id_user'];
                // Couleurs badge
                $catColorMap = ['general'=>'#6B7280','stage'=>'#8B5CF6','job'=>'#059669','question'=>'#F59E0B','evenement'=>'#EF4444'];
                $catBgMap    = ['general'=>'#F3F4F6','stage'=>'#EDE9FE','job'=>'#D1FAE5','question'=>'#FEF3C7','evenement'=>'#FEE2E2'];
                $cc = $catColorMap[$pub['categorie']] ?? '#6B7280';
                $cb = $catBgMap[$pub['categorie']]    ?? '#F3F4F6';
            ?>
            <div class="pub-card">
                <?php if (!empty($pub['image'])): ?>
                    <img src="../../public/uploads/publications/<?php echo htmlspecialchars($pub['image']); ?>"
                         alt="image" class="card-img">
                <?php else: ?>
                    <div class="card-img-placeholder" style="background:<?php echo $cc; ?>;"></div>
                <?php endif; ?>

                <div class="card-body">
                    <div class="card-top">
                        <span class="cat-badge" style="--cat-color:<?php echo $cc; ?>;--cat-bg:<?php echo $cb; ?>;background:<?php echo $cb; ?>;color:<?php echo $cc; ?>;">
                            <?php echo $cat['icon']; ?> <?php echo $cat['label']; ?>
                        </span>
                        <span class="card-date"><?php echo htmlspecialchars($pub['date_publication']); ?></span>
                    </div>

                    <div class="card-title">
                        <a href="index.php?action=voir&id=<?php echo $pub['id_publication']; ?>">
                            <?php echo htmlspecialchars($pub['titre']); ?>
                        </a>
                    </div>

                    <?php if ($pub['is_event']): ?>
                        <div class="event-box">
                            <strong>📅 Événement</strong>
                            <?php if ($pub['event_date']): ?>📆 <?php echo htmlspecialchars($pub['event_date']); ?><?php endif; ?>
                            <?php if ($pub['event_lieu']): ?> · 📍 <?php echo htmlspecialchars($pub['event_lieu']); ?><?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card-content">
                        <?php echo htmlspecialchars(mb_substr($pub['contenu'], 0, 140)); ?>…
                    </div>

                    <div class="card-footer-row">
                        <div class="meta-left">
                            <!-- Like -->
                            <button class="btn-like <?php echo $hasLiked ? 'liked' : ''; ?>"
                                    data-id="<?php echo $pub['id_publication']; ?>"
                                    onclick="toggleLike(this)"
                                    <?php echo !$id_user_session ? 'title="Connectez-vous pour liker" disabled' : ''; ?>>
                                <span class="heart"><?php echo $hasLiked ? '❤️' : '🤍'; ?></span>
                                <span class="like-count"><?php echo (int)$pub['nb_likes']; ?></span>
                            </button>
                            <!-- Vues -->
                            <span class="meta-stat">👁️ <?php echo (int)$pub['nb_vues']; ?></span>
                            <!-- Auteur -->
                            <span class="author-chip">👤 <?php echo htmlspecialchars(mb_substr($auteur, 0, 18)); ?></span>
                        </div>
                        <?php if ($isOwner): ?>
                            <div class="actions">
                                <a href="index.php?action=editPublication&id=<?php echo $pub['id_publication']; ?>" class="btn-edit-sm">✏️</a>
                                <a href="index.php?action=deletePublication&id=<?php echo $pub['id_publication']; ?>"
                                   class="btn-del-sm"
                                   onclick="return confirm('Supprimer cette publication ?');">🗑️</a>
                            </div>
                        <?php endif; ?>
                        <!-- Bouton Commenter -->
                        <button class="btn-comment"
                                onclick="toggleCommentPanel(this, <?php echo (int)$pub['id_publication']; ?>)">
                            💬 Commenter
                        </button>
                    </div>
                </div>

                <!-- Panneau commentaire inline -->
                <div class="comment-panel" id="cpanel-<?php echo (int)$pub['id_publication']; ?>">
                    <form method="POST" action="index.php?action=addComment" novalidate
                          onsubmit="return validateComment(this, <?php echo (int)$pub['id_publication']; ?>)">
                        <input type="hidden" name="id_publication" value="<?php echo (int)$pub['id_publication']; ?>">
                        <textarea name="contenu"
                                  id="ctxt-<?php echo (int)$pub['id_publication']; ?>"
                                  placeholder="Écrivez votre commentaire… (3–500 caractères)"
                                  oninput="onCommentInput(this, <?php echo (int)$pub['id_publication']; ?>)"></textarea>
                        <div class="err-msg" id="cerr-<?php echo (int)$pub['id_publication']; ?>"></div>
                        <div class="panel-footer">
                            <span class="char-info" id="cchar-<?php echo (int)$pub['id_publication']; ?>">0 / 500</span>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <button type="button" class="btn-cancel"
                                        onclick="toggleCommentPanel(this.closest('.comment-panel').previousElementSibling.querySelector('.btn-comment'), <?php echo (int)$pub['id_publication']; ?>)">
                                    Annuler
                                </button>
                                <button type="submit" class="btn-send">Envoyer 💬</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
            $buildUrl = function($p) use ($search_val, $cat_val, $tri_val) {
                $url = "index.php?action=list&page=$p&tri=" . urlencode($tri_val);
                if ($search_val) $url .= "&search=" . urlencode($search_val);
                if ($cat_val)    $url .= "&categorie=" . urlencode($cat_val);
                return $url;
            };
        ?>
        <div class="pagination-wrap">
            <a href="<?php echo $buildUrl($page_val - 1); ?>" class="page-btn <?php echo $page_val <= 1 ? 'disabled' : ''; ?>">‹</a>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="<?php echo $buildUrl($p); ?>" class="page-btn <?php echo $p === $page_val ? 'active' : ''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
            <a href="<?php echo $buildUrl($page_val + 1); ?>" class="page-btn <?php echo $page_val >= $totalPages ? 'disabled' : ''; ?>">›</a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// ── Like publication ──────────────────────────────────────────────────────────
function toggleLike(btn) {
    const id    = btn.dataset.id;
    const heart = btn.querySelector('.heart');
    const count = btn.querySelector('.like-count');

    fetch('index.php?action=like', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        if (data.action === 'liked') {
            btn.classList.add('liked');
            heart.textContent = '❤️';
        } else {
            btn.classList.remove('liked');
            heart.textContent = '🤍';
        }
        count.textContent = data.nb_likes;
    })
    .catch(() => alert('Erreur réseau'));
}

// ── Ouvrir / fermer le panneau commentaire ────────────────────────────────────
function toggleCommentPanel(btn, id) {
    const panel = document.getElementById('cpanel-' + id);
    if (!panel) return;

    const isOpen = panel.classList.contains('open');

    // Fermer tous les autres panneaux ouverts
    document.querySelectorAll('.comment-panel.open').forEach(p => {
        p.classList.remove('open');
        const otherBtn = p.closest('.pub-card').querySelector('.btn-comment');
        if (otherBtn) otherBtn.classList.remove('open');
    });

    if (!isOpen) {
        panel.classList.add('open');
        btn.classList.add('open');
        const ta = document.getElementById('ctxt-' + id);
        if (ta) setTimeout(() => ta.focus(), 50);
    }
}

// ── Compteur de caractères en temps réel ─────────────────────────────────────
function onCommentInput(ta, id) {
    const len      = ta.value.length;
    const charEl   = document.getElementById('cchar-' + id);
    const errEl    = document.getElementById('cerr-' + id);

    charEl.textContent = len + ' / 500';
    charEl.className   = 'char-info' + (len > 450 ? ' warn' : '');

    if (len > 0) {
        errEl.style.display = 'none';
        ta.classList.remove('error');
    }
}

// ── Validation avant envoi ────────────────────────────────────────────────────
function validateComment(form, id) {
    const ta    = document.getElementById('ctxt-' + id);
    const errEl = document.getElementById('cerr-' + id);
    const val   = ta.value.trim();

    const showErr = (msg) => {
        errEl.textContent    = '⚠️ ' + msg;
        errEl.style.display  = 'block';
        ta.classList.add('error');
        ta.focus();
        return false;
    };

    if (val.length === 0)   return showErr('Le commentaire ne peut pas être vide.');
    if (val.length < 3)     return showErr('Minimum 3 caractères requis.');
    if (val.length > 500)   return showErr('Maximum 500 caractères autorisés.');
    if (/^[^a-zA-Z0-9\u00C0-\u024F]+$/.test(val))
                            return showErr('Veuillez saisir un commentaire valide.');

    return true; // soumettre le formulaire
}
</script>