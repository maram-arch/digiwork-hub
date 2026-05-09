<?php
/**
 * Packs page — content only, rendered inside index.php layout.
 * No session_start(), no head, no body, no navbar.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../model/Pack.php';

$flash = $_SESSION['flash'] ?? null;
if ($flash) unset($_SESSION['flash']);

$pm = new Pack();
try {
    $allPacks = $pm->getAll()->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $allPacks = [];
}
?>

<style>
  .packs-page { max-width: 1100px; margin: 0 auto; padding: 40px 20px; }
  .packs-hero { background: linear-gradient(135deg, #1b4379, #2270c1); color: #fff; border-radius: 16px; padding: 40px 30px; margin-bottom: 40px; text-align: center; }
  .packs-hero h1 { font-size: 28px; font-weight: 800; margin-bottom: 10px; }
  .packs-hero p  { font-size: 16px; opacity: .9; }
  .packs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px; }
  .pack-card-front { background: #fff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; display: flex; flex-direction: column; transition: transform .2s, box-shadow .2s; }
  .pack-card-front:hover { transform: translateY(-4px); box-shadow: 0 14px 32px rgba(0,0,0,.13); }
  .pack-card-header-front { background: linear-gradient(135deg, #1b4379, #2270c1); color: #fff; padding: 24px 20px; text-align: center; }
  .pack-card-header-front h3 { font-size: 20px; font-weight: 800; margin: 0 0 6px; }
  .pack-card-header-front .price { font-size: 32px; font-weight: 800; }
  .pack-card-header-front .price span { font-size: 16px; font-weight: 400; opacity: .8; }
  .pack-card-body { padding: 20px; flex: 1; }
  .pack-card-body p { font-size: 13px; color: #555; line-height: 1.6; white-space: pre-line; }
  .pack-card-body .meta { margin-top: 12px; font-size: 13px; color: #777; }
  .pack-card-footer { padding: 16px 20px; border-top: 1px solid #eee; }
  .pack-card-footer button { width: 100%; padding: 12px; background: linear-gradient(135deg, #00A651, #008040); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: opacity .2s; }
  .pack-card-footer button:hover { opacity: .88; }
  /* Subscribe modal */
  #packSubscribeModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; align-items:center; justify-content:center; }
  #packSubscribeModal.open { display:flex; }
  .psm-box { background:#fff; border-radius:16px; padding:32px; width:90%; max-width:460px; box-shadow:0 16px 48px rgba(0,0,0,.18); }
  .psm-box h2 { margin:0 0 20px; font-size:20px; color:#1b4379; }
  .psm-box label { display:block; margin-bottom:6px; font-size:14px; font-weight:600; color:#333; }
  .psm-box input, .psm-box select { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; margin-bottom:14px; }
  .psm-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:6px; }
  .psm-actions button { padding:10px 22px; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; }
  .psm-cancel { background:#eee; color:#333; }
  .psm-confirm { background:#00A651; color:#fff; }
</style>

<div class="packs-page">

  <?php if ($flash): ?>
    <div style="background:#D1FAE5;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;">
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <div class="packs-hero">
    <h1>Nos Packs</h1>
    <p>Choisissez le pack adapté à votre activité et boostez votre carrière digitale.</p>
  </div>

  <div class="packs-grid">
    <?php if (empty($allPacks)): ?>
      <p style="color:#888;text-align:center;grid-column:1/-1;">Aucun pack disponible pour le moment.</p>
    <?php else: ?>
      <?php foreach ($allPacks as $p): ?>
      <div class="pack-card-front">
        <div class="pack-card-header-front">
          <h3><?= htmlspecialchars($p['nom-pack'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="price">
            <?= htmlspecialchars((string)($p['prix'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> DT
            <span>/ pack</span>
          </div>
        </div>
        <div class="pack-card-body">
          <p><?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          <div class="meta">
            <strong>Projets max :</strong> <?= htmlspecialchars((string)($p['nb-proj-max'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br>
            <strong>Support prioritaire :</strong> <?= htmlspecialchars($p['support-prioritaire'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>
        <div class="pack-card-footer">
          <button onclick="openPackModal(<?= (int)($p['id-pack'] ?? 0) ?>, '<?= htmlspecialchars($p['nom-pack'] ?? '', ENT_QUOTES) ?>')">
            S'abonner
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- Subscribe Modal -->
<div id="packSubscribeModal">
  <div class="psm-box">
    <h2 id="psmTitle">S'abonner</h2>
    <input type="hidden" id="psmPackId">
    <label>Date de début</label>
    <input type="date" id="psmDateDeb">
    <label>Date de fin</label>
    <input type="date" id="psmDateFin">
    <label>Statut</label>
    <select id="psmStatut">
      <option value="actif">Actif</option>
      <option value="en_attente">En attente</option>
    </select>
    <div id="psmAlert" style="display:none;padding:10px;border-radius:8px;font-size:13px;margin-bottom:10px;"></div>
    <div class="psm-actions">
      <button class="psm-cancel" onclick="closePackModal()">Annuler</button>
      <button class="psm-confirm" onclick="confirmSubscribe()">Confirmer</button>
    </div>
  </div>
</div>

<script>
function openPackModal(packId, packName) {
  document.getElementById('psmPackId').value = packId;
  document.getElementById('psmTitle').textContent = 'S\'abonner — ' + packName;
  var today = new Date().toISOString().split('T')[0];
  var next = new Date(); next.setMonth(next.getMonth() + 1);
  document.getElementById('psmDateDeb').value = today;
  document.getElementById('psmDateFin').value = next.toISOString().split('T')[0];
  document.getElementById('psmAlert').style.display = 'none';
  document.getElementById('packSubscribeModal').classList.add('open');
}
function closePackModal() {
  document.getElementById('packSubscribeModal').classList.remove('open');
}
function showPsmAlert(msg, ok) {
  var el = document.getElementById('psmAlert');
  el.textContent = msg;
  el.style.display = 'block';
  el.style.background = ok ? '#D1FAE5' : '#FEE2E2';
  el.style.color = ok ? '#065F46' : '#991B1B';
}
function confirmSubscribe() {
  var fd = new FormData();
  fd.append('action', 'subscribe');
  fd.append('ajax', '1');
  fd.append('pack_id', document.getElementById('psmPackId').value);
  fd.append('date_deb', document.getElementById('psmDateDeb').value);
  fd.append('date_fin', document.getElementById('psmDateFin').value);
  fd.append('status', document.getElementById('psmStatut').value);
  fetch('/projectttttttt/controller/AbonnementController.php', { method: 'POST', body: fd })
    .then(function(r){
      if (!r.ok && r.status !== 401 && r.status !== 400) {
        throw new Error('HTTP ' + r.status);
      }
      return r.json();
    })
    .then(function(d){
      if (d.status === 'success') {
        showPsmAlert('✅ Abonnement créé avec succès !', true);
        setTimeout(closePackModal, 1800);
      } else if (d.status === 'error' && d.message && d.message.indexOf('connecté') !== -1) {
        showPsmAlert('⚠️ ' + d.message, false);
        setTimeout(function(){
          closePackModal();
          if (typeof openModal === 'function') openModal('loginModal');
        }, 1500);
      } else {
        showPsmAlert('❌ ' + (d.message || 'Erreur'), false);
      }
    })
    .catch(function(err){ showPsmAlert('❌ Erreur : ' + err.message, false); });
}
// Close on backdrop click
document.getElementById('packSubscribeModal').addEventListener('click', function(e){
  if (e.target === this) closePackModal();
});
</script>
