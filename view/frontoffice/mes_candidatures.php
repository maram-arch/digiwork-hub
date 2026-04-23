<?php
// frontoffice/views/mes_candidatures.php
// ★ getCandidaturesByUser() utilise maintenant INNER JOIN offre
 
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
 
$dbTemp  = Config::getConnexion();
$id_user = (int)$dbTemp->query(
    "SELECT id_user FROM user ORDER BY id_user ASC LIMIT 1"
)->fetchColumn();
if ($id_user <= 0) $id_user = 14;
 
$controller   = new CandidatureController();
// ★ Retourne maintenant un tableau avec titre_offre, adresse, type_offre, date_limite
$candidatures = $controller->getCandidaturesByUser($id_user);
 
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
    <title>Mes candidatures - DigiWork Hub</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6fb; color:#2d3748; }
 
        .navbar { background:#fff; padding:14px 32px; display:flex;
                  justify-content:space-between; align-items:center;
                  box-shadow:0 2px 8px rgba(0,0,0,.07); }
        .navbar img { height:40px; }
        .btn-retour { background:#fff; color:#435ebe; border:1.5px solid #435ebe;
                      border-radius:8px; padding:8px 18px; font-size:13px;
                      font-weight:600; text-decoration:none; transition:all .2s; }
        .btn-retour:hover { background:#435ebe; color:#fff; }
 
        .container  { max-width:1150px; margin:0 auto; padding:36px 24px; }
        .page-header { margin-bottom:28px; }
        .page-header h3 { font-size:24px; font-weight:800; color:#1a202c; margin-bottom:4px; }
        .page-header p  { font-size:14px; color:#6c757d; }
 
        .alert { padding:14px 18px; border-radius:10px; margin-bottom:24px;
                 font-size:14px; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#d1e7dd; color:#0f5132; border-left:4px solid #1d9e75; }
        .alert-danger  { background:#f8d7da; color:#842029; border-left:4px solid #dc3545; }
 
        .card { background:#fff; border-radius:16px;
                box-shadow:0 4px 24px rgba(67,94,190,.08); overflow:hidden; }
        .card-header { padding:20px 24px; border-bottom:1px solid #f0f1f5;
                       display:flex; align-items:center; justify-content:space-between; }
        .card-header h4 { font-size:16px; font-weight:700; color:#2d3748; }
        .card-header span { font-size:13px; color:#6c757d; }
 
        table { width:100%; border-collapse:collapse; }
        thead tr { background:#f8f9ff; }
        thead th { padding:14px 18px; font-size:11px; font-weight:700; color:#6c757d;
                   text-transform:uppercase; letter-spacing:.06em;
                   border-bottom:2px solid #f0f1f5; white-space:nowrap; }
        tbody tr { border-bottom:1px solid #f5f6fa; transition:background .15s; }
        tbody tr:hover { background:#fafbff; }
        tbody td { padding:16px 18px; font-size:14px; vertical-align:middle; }
        tbody tr:last-child { border-bottom:none; }
 
        /* ★ Cellule offre enrichie par la jointure */
        .offre-title  { font-weight:700; color:#1a202c; font-size:14px; margin-bottom:2px; }
        .offre-addr   { font-size:12px; color:#6c757d; }
        .offre-type   { display:inline-block; background:#e6f1fb; color:#185fa5;
                        border-radius:20px; padding:2px 8px; font-size:11px;
                        font-weight:600; margin-bottom:3px; }
        .offre-dl     { font-size:11px; color:#adb5bd; margin-top:2px; }
 
        .cv-link { display:inline-flex; align-items:center; gap:5px;
                   background:#e6f1fb; color:#185fa5; border-radius:6px;
                   padding:4px 10px; font-size:12px; font-weight:600;
                   text-decoration:none; transition:background .15s; }
        .cv-link:hover { background:#c5dff5; }
 
        .lettre-text { max-width:180px; overflow:hidden; text-overflow:ellipsis;
                       white-space:nowrap; font-size:13px; color:#6c757d; }
 
        .date-badge { background:#f5f6fa; color:#6c757d; border-radius:6px;
                      padding:4px 10px; font-size:12px; font-weight:600;
                      white-space:nowrap; }
 
        .badge { display:inline-flex; align-items:center; gap:5px;
                 padding:5px 14px; border-radius:20px;
                 font-size:12px; font-weight:700; white-space:nowrap; }
        .badge-attente { background:#faeeda; color:#854f0b; }
        .badge-accepte { background:#e1f5ee; color:#0f6e56; }
        .badge-refuse  { background:#fcebeb; color:#a32d2d; }
 
        .btn-modifier  { background:#fff3e0; color:#fd7e14; border:1px solid #ffc680;
                         border-radius:6px; padding:6px 12px; font-size:12px;
                         font-weight:600; cursor:pointer; display:inline-flex;
                         align-items:center; gap:4px; transition:background .15s; }
        .btn-modifier:hover  { background:#ffe0b2; }
        .btn-supprimer { background:#fcebeb; color:#a32d2d; border:1px solid #f09595;
                         border-radius:6px; padding:6px 12px; font-size:12px;
                         font-weight:600; cursor:pointer; display:inline-flex;
                         align-items:center; gap:4px; transition:background .15s; }
        .btn-supprimer:hover { background:#fbd5d5; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
 
        /* MODALES */
        .modal-overlay { display:none; position:fixed; inset:0;
                         background:rgba(0,0,0,.52); z-index:9999;
                         justify-content:center; align-items:center; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:#fff; border-radius:16px; width:100%; max-width:520px;
                     max-height:92vh; overflow-y:auto;
                     box-shadow:0 24px 64px rgba(0,0,0,.25);
                     animation:mIn .22s ease; }
        @keyframes mIn { from{transform:translateY(-20px);opacity:0}
                         to{transform:translateY(0);opacity:1} }
        .modal-top { display:flex; justify-content:space-between; align-items:center;
                     padding:18px 24px 14px; border-bottom:1px solid #f0f1f5; }
        .modal-top h5 { margin:0; font-size:16px; font-weight:700; color:#2d3748; }
        .btn-close-x { background:#f5f6fa; border:none; border-radius:8px;
                       width:32px; height:32px; font-size:18px; cursor:pointer;
                       color:#888; display:flex; align-items:center;
                       justify-content:center; transition:background .15s; }
        .btn-close-x:hover { background:#ffe5e5; color:#e74c3c; }
        .modal-body { padding:20px 24px; }
        .modal-foot { padding:14px 24px; border-top:1px solid #f0f1f5;
                      display:flex; justify-content:flex-end; gap:10px;
                      background:#fafbfc; border-radius:0 0 16px 16px; }
        .field-label { font-size:11px; font-weight:700; color:#6c757d;
                       text-transform:uppercase; letter-spacing:.05em;
                       margin-bottom:6px; display:block; }
        .m-input { font-size:14px; border-radius:8px; border:1px solid #dee2e6;
                   width:100%; padding:8px 12px; resize:vertical; }
        .m-input:focus { border-color:#435ebe; outline:none;
                         box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .char-count { font-size:12px; color:#adb5bd; margin-top:4px; }
        .err { font-size:12px; color:#dc3545; margin-top:4px; display:none; }
        .err.show { display:block; }
        .btn-cancel { background:#f5f6fa; border:none; color:#666; border-radius:8px;
                      padding:10px 20px; font-size:14px; cursor:pointer; }
        .btn-save   { background:#435ebe; border:none; color:#fff; border-radius:8px;
                      padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-voir-offres { display:inline-block; background:#435ebe; color:#fff;
                           border-radius:8px; padding:10px 24px; font-size:14px;
                           font-weight:600; text-decoration:none; margin-top:12px; }
        .empty-state { text-align:center; padding:48px 24px; }
        .empty-icon  { font-size:48px; margin-bottom:12px; }
    </style>
</head>
<body>
 
<nav class="navbar">
    <img src="assets/img/logo/logo.png" alt="DigiWork Hub">
    <a href="offres.php" class="btn-retour">← Retour aux offres</a>
</nav>
 
<div class="container">
    <div class="page-header">
        <h3>📋 Mes candidatures</h3>
        <p>Suivez l'état de toutes vos candidatures</p>
    </div>
 
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
    </div>
    <?php endif; ?>
 
    <div class="card">
        <div class="card-header">
            <h4>Mes candidatures</h4>
            <span><?= count($candidatures) ?> candidature(s)</span>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <!-- ★ Colonne enrichie par la jointure -->
                        <th>Offre (titre · type · adresse)</th>
                        <th>CV</th>
                        <th>Lettre</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($candidatures) > 0): ?>
                    <?php foreach ($candidatures as $c): ?>
                    <?php $s = $c['Statut'] ?? 'en attente'; ?>
                    <tr>
                        <!-- ★ Données offre issues de la jointure -->
                        <td>
                            <div class="offre-type">
                                <?= htmlspecialchars($c['type_offre'] ?? '') ?>
                            </div>
                            <div class="offre-title">
                                <?= htmlspecialchars($c['titre_offre'] ?? '—') ?>
                            </div>
                            <div class="offre-addr">
                                📍 <?= htmlspecialchars($c['adresse'] ?? '—') ?>
                            </div>
                            <?php if (!empty($c['date_limite'])): ?>
                            <div class="offre-dl">
                                ⏰ Limite : <?= htmlspecialchars($c['date_limite']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
 
                        <td>
                            <?php if (!empty($c['cv'])): ?>
                            <a href="assets/uploads/cv/<?= htmlspecialchars($c['cv']) ?>"
                               target="_blank" class="cv-link">
                                📄 Voir CV
                            </a>
                            <?php else: ?>
                            <span style="color:#adb5bd">—</span>
                            <?php endif; ?>
                        </td>
 
                        <td>
                            <div class="lettre-text"
                                 title="<?= htmlspecialchars($c['Lettre'] ?? '') ?>">
                                <?= htmlspecialchars($c['Lettre'] ?? '—') ?>
                            </div>
                        </td>
 
                        <td>
                            <span class="date-badge">
                                📅 <?= htmlspecialchars($c['Date'] ?? '—') ?>
                            </span>
                        </td>
 
                        <td>
                            <?php if ($s === 'en attente'): ?>
                                <span class="badge badge-attente">⏳ En attente</span>
                            <?php elseif ($s === 'accepte'): ?>
                                <span class="badge badge-accepte">✅ Accepté</span>
                            <?php else: ?>
                                <span class="badge badge-refuse">❌ Refusé</span>
                            <?php endif; ?>
                        </td>
 
                        <td>
                            <?php if ($s === 'en attente'): ?>
                            <div class="actions">
                                <button class="btn-modifier"
                                    data-id_user="<?= (int)$c['id_user'] ?>"
                                    data-id_offer="<?= (int)$c['id_offer'] ?>"
                                    data-lettre="<?= htmlspecialchars(
                                        $c['Lettre'] ?? '', ENT_QUOTES) ?>"
                                    onclick="openEditModal(this)">
                                    ✏️ Modifier
                                </button>
                                <button class="btn-supprimer"
                                    data-id_user="<?= (int)$c['id_user'] ?>"
                                    data-id_offer="<?= (int)$c['id_offer'] ?>"
                                    data-titre="<?= htmlspecialchars(
                                        $c['titre_offre'] ?? '', ENT_QUOTES) ?>"
                                    onclick="openDeleteModal(this)">
                                    🗑️ Retirer
                                </button>
                            </div>
                            <?php else: ?>
                            <span style="color:#adb5bd;font-size:13px">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <p>Vous n'avez pas encore postulé à une offre.</p>
                                <a href="offres.php" class="btn-voir-offres">
                                    🔍 Voir les offres
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
 
<!-- MODALE MODIFIER -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>✏️ Modifier ma candidature</h5>
            <button class="btn-close-x" onclick="closeEditModal()">✕</button>
        </div>
        <form method="POST" action="modifierCandidature.php" id="editForm"
              enctype="multipart/form-data" onsubmit="return validateEdit(event)">
            <input type="hidden" name="id_user"  id="edit_id_user">
            <input type="hidden" name="id_offer" id="edit_id_offer">
            <div class="modal-body">
                <div style="margin-bottom:16px">
                    <label class="field-label">
                        📎 Nouveau CV (laisser vide pour garder l'ancien)
                    </label>
                    <input type="file" class="m-input" name="cv" id="edit_cv"
                           accept=".pdf,.doc,.docx">
                    <div class="char-count">PDF, DOC, DOCX — max 2 Mo</div>
                    <div class="err" id="err_cv"></div>
                </div>
                <div>
                    <label class="field-label">📝 Lettre de motivation *</label>
                    <textarea class="m-input" name="lettre_motivation" id="edit_lettre"
                              rows="6" oninput="updateEditCount()"></textarea>
                    <div class="char-count" id="edit_count">0 / 2000 caractères</div>
                    <div class="err" id="err_lettre"></div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel"
                        onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>
 
<!-- MODALE SUPPRIMER -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>🗑️ Supprimer la candidature</h5>
            <button class="btn-close-x" onclick="closeDeleteModal()">✕</button>
        </div>
        <div class="modal-body" style="text-align:center;padding:32px 24px">
            <div style="width:64px;height:64px;background:#ffe5e5;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        margin:0 auto 16px;font-size:28px">🗑️</div>
            <h6 style="font-size:16px;font-weight:700;color:#2d3748;margin-bottom:8px">
                Êtes-vous sûr ?
            </h6>
            <p style="color:#6c757d;font-size:14px;margin-bottom:6px">
                Vous allez retirer votre candidature pour :
            </p>
            <p id="delete-titre"
               style="font-weight:700;color:#dc3545;font-size:15px;margin-bottom:6px">
            </p>
            <p style="color:#adb5bd;font-size:12px">Cette action est irréversible.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button class="btn-cancel" onclick="closeDeleteModal()"
                    style="padding:10px 28px">Annuler</button>
            <a id="delete-confirm" href="#"
               style="background:#dc3545;color:#fff;border-radius:8px;
                      padding:10px 28px;font-size:14px;font-weight:600;
                      text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                🗑️ Oui, supprimer
            </a>
        </div>
    </div>
</div>
 
<script>
function openEditModal(btn) {
    document.getElementById('edit_id_user').value  = btn.dataset.id_user;
    document.getElementById('edit_id_offer').value = btn.dataset.id_offer;
    document.getElementById('edit_lettre').value   = btn.dataset.lettre;
    document.getElementById('edit_count').textContent =
        btn.dataset.lettre.length + ' / 2000 caractères';
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}
function updateEditCount() {
    var len = document.getElementById('edit_lettre').value.length;
    document.getElementById('edit_count').textContent = len + ' / 2000 caractères';
}
function validateEdit(e) {
    e.preventDefault();
    var lettre = document.getElementById('edit_lettre').value.trim();
    var err    = document.getElementById('err_lettre');
    if (lettre.length < 10) {
        err.textContent = 'Minimum 10 caractères requis';
        err.classList.add('show');
        return false;
    }
    err.classList.remove('show');
    document.getElementById('editForm').submit();
    return false;
}
function openDeleteModal(btn) {
    document.getElementById('delete-titre').textContent = btn.dataset.titre;
    document.getElementById('delete-confirm').href =
        'supprimerCandidature.php?id_user=' + btn.dataset.id_user +
        '&id_offer=' + btn.dataset.id_offer;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeEditModal(); closeDeleteModal(); }
});
</script>
</body>
</html>