<?php
require_once __DIR__ . '/../../model/Publication.php';
$topPubs = Publication::getTopPublications(5);
$trending = Publication::getTrending(5);
$topUsers = Publication::getTopUsers(5);
$engagement = Publication::getEngagementByCategory();
$activity = Publication::getActivityTimeline();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques du forum - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f0f2f9; }
        body.dark-mode { background: #1e1e2f; color: #eee; }
        .card { border-radius: 20px; margin-bottom: 20px; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: #435ebe; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; z-index: 1000; }
    </style>
</head>

<body>
<header class="header header-6"><?php // header identique ?></header>
<div class="container mt-4">
    <h2>📊 Statistiques du forum</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card p-3"><h4>🔥 Top publications (score qualité)</h4><ul><?php foreach($topPubs as $p): ?><li><?= htmlspecialchars($p['titre']) ?> – Score <?= round($p['score'],1) ?></li><?php endforeach; ?></ul></div>
        </div>
        <div class="col-md-6">
            <div class="card p-3"><h4>⚡ Trending aujourd'hui</h4><ul><?php foreach($trending as $p): ?><li><?= htmlspecialchars($p['titre']) ?></li><?php endforeach; ?></ul></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card p-3"><h4>🏆 Top contributeurs</h4><ul><?php foreach($topUsers as $u): ?><li><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?> (score <?= $u['score'] ?>)</li><?php endforeach; ?></ul></div>
        </div>
        <div class="col-md-6">
            <div class="card p-3"><h4>📈 Engagement par catégorie</h4><canvas id="catChart" width="400" height="200"></canvas></div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card p-3"><h4>📅 Activité 7 derniers jours</h4><canvas id="activityChart"></canvas></div>
        </div>
    </div>
</div>
<button id="themeToggle" class="theme-toggle">🌓</button>
<script>
const catCtx = document.getElementById('catChart').getContext('2d');
new Chart(catCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($engagement, 'categorie')) ?>,
        datasets: [
            { label: 'Publications', data: <?= json_encode(array_column($engagement, 'nb_pubs')) ?>, backgroundColor: '#435ebe' },
            { label: 'Likes', data: <?= json_encode(array_column($engagement, 'total_likes')) ?>, backgroundColor: '#f1c40f' }
        ]
    }
});
// Activité (simplifié)
const activityData = <?php
    $dates = [];
    for($i=6;$i>=0;$i--) $dates[] = date('Y-m-d', strtotime("-$i days"));
    $pubsByDate = [];
    $comsByDate = [];
    foreach($activity['publications'] as $p) $pubsByDate[$p['jour']] = $p['nb'];
    foreach($activity['commentaires'] as $c) $comsByDate[$c['jour']] = $c['nb'];
    $pubsData = []; $comsData = [];
    foreach($dates as $d) { $pubsData[] = $pubsByDate[$d] ?? 0; $comsData[] = $comsByDate[$d] ?? 0; }
    echo json_encode(['labels'=>$dates, 'pubs'=>$pubsData, 'coms'=>$comsData]);
?>;
const actCtx = document.getElementById('activityChart').getContext('2d');
new Chart(actCtx, {
    type: 'line',
    data: { labels: activityData.labels, datasets: [{ label: 'Publications', data: activityData.pubs, borderColor: '#435ebe' }, { label: 'Commentaires', data: activityData.coms, borderColor: '#e74c3c' }] }
});
// Mode sombre
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
</script>
</body>
</html>