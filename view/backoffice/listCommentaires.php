<?php include __DIR__ . '/layout_back.php'; ?>

<style>
.com-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
    gap:16px;
}

.com-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:14px;
    display:flex;
    flex-direction:column;
    gap:10px;
    transition:.2s;
}

.com-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
}

.com-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.com-id{
    font-size:11px;
    font-weight:700;
    padding:4px 10px;
    border-radius:20px;
    background:#ECFDF5;
    color:#065F46;
}

.com-pub{
    font-size:12px;
    color:#2F80ED;
    text-decoration:none;
    font-weight:600;
}

.com-text{
    font-size:13px;
    color:#374151;
    line-height:1.4;
    background:#F9FAFB;
    padding:10px;
    border-radius:8px;
}

.com-meta{
    font-size:11px;
    color:#6B7280;
    display:flex;
    flex-direction:column;
    gap:4px;
}

.com-actions{
    display:flex;
    gap:8px;
}
</style>

<div class="card">
    <div class="card-header">
        <h2>💬 Commentaires <span class="badge badge-green"><?= count($commentaires) ?> total</span></h2>
    </div>

    <div class="card-body">

        <?php if (empty($commentaires)): ?>
            <p style="color:#9CA3AF;text-align:center;padding:30px;">
                Aucun commentaire.
            </p>
        <?php else: ?>

        <div class="com-grid">

            <?php foreach ($commentaires as $c): ?>
                <div class="com-card">

                    <div class="com-top">
                        <span class="com-id">#<?= (int)$c['id_commentaire'] ?></span>

                        <a class="com-pub"
                           href="index.php?action=detail&id=<?= (int)$c['id_publication'] ?>">
                           <?= htmlspecialchars($c['pub_titre'] ?? '-') ?>
                        </a>
                    </div>

                    <div class="com-text">
                        <?= htmlspecialchars(mb_substr($c['contenu'], 0, 120)) ?>
                        <?= mb_strlen($c['contenu']) > 120 ? '...' : '' ?>
                    </div>

                    <div class="com-meta">
                        <div>📅 Créé : <?= htmlspecialchars($c['date_creation']) ?></div>
                        <div>
                            🛠 Modif :
                            <?= !empty($c['date_modification'])
                                ? htmlspecialchars($c['date_modification'])
                                : '—' ?>
                        </div>
                    </div>

                    <div class="com-actions">
                        <a href="index.php?action=deleteComment&id=<?= (int)$c['id_commentaire'] ?>&pub=<?= (int)$c['id_publication'] ?>"
                           onclick="return confirm('Supprimer ce commentaire ?')"
                           class="btn btn-danger btn-sm">🗑️</a>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/layout_end.php'; ?>