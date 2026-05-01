<?php
require_once __DIR__ . '/../../controller/OffreController.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

$offreController = new OffreController();
$offres = $offreController->listOffre()->fetchAll(PDO::FETCH_ASSOC);

$candController = new CandidatureController();
$candidatures = $candController->getAllCandidatures();

$totalOffres = count($offres);
$totalCandidatures = count($candidatures);
$parType = [];
$expirees = 0;
$today = date('Y-m-d');

foreach ($offres as $offre) {
    $type = $offre['type'] ?? 'Autre';
    $parType[$type] = ($parType[$type] ?? 0) + 1;
    if (!empty($offre['date_limite']) && $offre['date_limite'] < $today) {
        $expirees++;
    }
}
arsort($parType);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistique offres - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body{background:#f2f4fb}
        .stats-wrap{max-width:1100px;margin:36px auto;padding:0 18px}
        .stats-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
        .stats-actions{display:flex;gap:10px;align-items:center}
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
        .stat-card{background:#fff;border-radius:12px;padding:22px;border:1px solid #edf0f7;box-shadow:0 4px 18px rgba(67,94,190,.08)}
        .stat-label{font-size:12px;color:#6c757d;text-transform:uppercase;font-weight:700;letter-spacing:.05em}
        .stat-value{font-size:34px;font-weight:800;color:#435ebe;margin-top:8px}
        .type-row{display:flex;align-items:center;gap:12px;margin-bottom:14px}
        .type-name{width:120px;font-weight:700;color:#2d3748}
        .bar{height:12px;background:#eef1fb;border-radius:20px;flex:1;overflow:hidden}
        .bar span{display:block;height:100%;background:#435ebe;border-radius:20px}
        @media(max-width:768px){.stats-grid{grid-template-columns:1fr}.stats-head{align-items:flex-start;flex-direction:column;gap:12px}.stats-actions{width:100%;flex-wrap:wrap}}
    </style>
</head>
<body>
    <div class="stats-wrap">
        <div class="stats-head">
            <div>
                <h3>Statistique des offres</h3>
                <p class="text-muted mb-0">Vue rapide sur les offres et candidatures</p>
            </div>
            <div class="stats-actions">
                <a href="addOffre.php" class="btn btn-success btn-sm">Ajouter</a>
                <a href="listOffres.php" class="btn btn-primary btn-sm">Retour aux offres</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total offres</div>
                <div class="stat-value"><?= $totalOffres ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Offres expirees</div>
                <div class="stat-value"><?= $expirees ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Candidatures</div>
                <div class="stat-value"><?= $totalCandidatures ?></div>
            </div>
        </div>

        <div class="stat-card">
            <h5 class="mb-4">Offres par type</h5>
            <?php if (!empty($parType)): ?>
                <?php foreach ($parType as $type => $count): ?>
                    <?php $percent = $totalOffres > 0 ? round(($count / $totalOffres) * 100) : 0; ?>
                    <div class="type-row">
                        <div class="type-name"><?= htmlspecialchars($type) ?></div>
                        <div class="bar"><span style="width:<?= $percent ?>%"></span></div>
                        <strong><?= $count ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted mb-0">Aucune offre disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
