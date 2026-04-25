<?php include __DIR__ . '/layout_back.php'; ?>

<style>
.dash-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:16px;
    margin-bottom:24px;
}

.dash-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:18px;
    display:flex;
    flex-direction:column;
    gap:8px;
    transition:.2s;
}

.dash-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
}

.dash-icon{
    font-size:22px;
}

.dash-label{
    font-size:12px;
    color:#6B7280;
    font-weight:600;
    text-transform:uppercase;
}

.dash-value{
    font-size:26px;
    font-weight:800;
    color:#111827;
}

.quick-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
    gap:12px;
}
</style>

<!-- STATS -->
<div class="dash-grid">

    <div class="dash-card" style="border-left:4px solid #2F80ED;">
        <div class="dash-icon">📝</div>
        <div class="dash-label">Publications</div>
        <div class="dash-value"><?= (int)($stats['pub'] ?? 0); ?></div>
    </div>

    <div class="dash-card" style="border-left:4px solid #10B981;">
        <div class="dash-icon">💬</div>
        <div class="dash-label">Commentaires</div>
        <div class="dash-value"><?= (int)($stats['com'] ?? 0); ?></div>
    </div>

    <div class="dash-card" style="border-left:4px solid #EF4444;">
        <div class="dash-icon">❤️</div>
        <div class="dash-label">Likes totaux</div>
        <div class="dash-value"><?= (int)($stats['likes'] ?? 0); ?></div>
    </div>

    <div class="dash-card" style="border-left:4px solid #F59E0B;">
        <div class="dash-icon">👁️</div>
        <div class="dash-label">Vues totales</div>
        <div class="dash-value"><?= (int)($stats['vues'] ?? 0); ?></div>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="card">
    <div class="card-header">
        <h2>⚡ Accès rapide</h2>
    </div>

    <div class="card-body">
        <div class="quick-grid">

            <a href="index.php?action=listPublications" class="btn btn-primary">
                📝 Publications
            </a>

            <a href="index.php?action=listCommentaires" class="btn btn-outline">
                💬 Commentaires
            </a>

            <a href="index.php?action=addPublication" class="btn btn-outline">
                ➕ Nouvelle publication
            </a>

            <a href="index.php?action=front" class="btn btn-outline">
                🏠 Voir forum
            </a>

        </div>
    </div>
</div>

<?php include __DIR__ . '/layout_end.php'; ?>