<?php include __DIR__ . '/layout_back.php'; ?>

<style>
.pub-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:16px;
}

.pub-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:16px;
    display:flex;
    flex-direction:column;
    gap:10px;
    transition:.2s;
}

.pub-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
}

.pub-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.pub-title{
    font-size:15px;
    font-weight:700;
    color:#111827;
}

.pub-badge{
    font-size:11px;
    padding:4px 10px;
    border-radius:20px;
    background:#EFF6FF;
    color:#1D4ED8;
    font-weight:600;
}

.pub-meta{
    font-size:12px;
    color:#6B7280;
    display:flex;
    flex-direction:column;
    gap:4px;
}

.pub-stats{
    display:flex;
    gap:10px;
    font-size:12px;
    color:#374151;
}

.pub-actions{
    display:flex;
    gap:8px;
    margin-top:8px;
}
</style>

<div class="card">
    <div class="card-header">
        <h2>📝 Publications <span class="badge badge-blue"><?= count($publications) ?> total</span></h2>
        <a href="index.php?action=addPublication" class="btn btn-primary btn-sm">➕ Nouvelle</a>
    </div>

    <div class="card-body">

        <?php if (empty($publications)): ?>
            <p style="color:#9CA3AF;text-align:center;padding:30px;">
                Aucune publication.
            </p>
        <?php else: ?>

        <div class="pub-grid">

            <?php foreach ($publications as $p): ?>
                <div class="pub-card">

                    <div class="pub-top">
                        <div class="pub-title">
                            <?= htmlspecialchars($p['titre']) ?>
                        </div>

                        <span class="pub-badge">
                            #<?= (int)$p['id_publication'] ?>
                        </span>
                    </div>

                    <div class="pub-meta">
                        <div>👤 <?= htmlspecialchars($p['auteur_nom'] ?? $p['auteur_email'] ?? '-') ?></div>
                        <div>📅 <?= htmlspecialchars($p['date_publication']) ?></div>
                        <div>🏷️ <?= htmlspecialchars($p['categorie'] ?? '-') ?></div>
                    </div>

                    <div class="pub-stats">
                        <span>❤️ <?= (int)$p['nb_likes'] ?></span>
                        <span>👁️ <?= (int)$p['nb_vues'] ?></span>
                    </div>

                    <div class="pub-actions">
                        <a href="index.php?action=detail&id=<?= (int)$p['id_publication'] ?>" class="btn btn-outline btn-sm">👁️</a>
                        <a href="index.php?action=editPublication&id=<?= (int)$p['id_publication'] ?>" class="btn btn-outline btn-sm">✏️</a>
                        <a href="index.php?action=deletePublication&id=<?= (int)$p['id_publication'] ?>"
                           onclick="return confirm('Supprimer cette publication ?')"
                           class="btn btn-danger btn-sm">🗑️</a>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/layout_end.php'; ?>