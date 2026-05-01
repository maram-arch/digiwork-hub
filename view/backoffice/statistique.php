<?php
if (!isset($totalCandidatures, $statsStatut)) {
    require_once __DIR__ . '/../../controller/CandidatureController.php';

    $controller = new CandidatureController();
    $allCandidatures = $controller->getAllCandidatures();
    $totalCandidatures = count($allCandidatures);
    $statsStatut = [
        'en_attente' => 0,
        'accepte' => 0,
        'refuse' => 0,
    ];

    foreach ($allCandidatures as $candStat) {
        $statut = $candStat['Statut'] ?? 'en_attente';
        $statsStatut[$statut] = ($statsStatut[$statut] ?? 0) + 1;
    }
}
?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total candidatures</div>
        <div class="stat-value"><?= $totalCandidatures ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">En attente</div>
        <div class="stat-value"><?= $statsStatut['en_attente'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Acceptees</div>
        <div class="stat-value"><?= $statsStatut['accepte'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Refusees</div>
        <div class="stat-value"><?= $statsStatut['refuse'] ?? 0 ?></div>
    </div>
</div>
<div class="chart-card">
    <h5 class="mb-3">Statistique des candidatures</h5>
    <div class="chart-wrap">
        <canvas id="candidatureStatChart"></canvas>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartCanvas = document.getElementById('candidatureStatChart');
    if (!chartCanvas || typeof Chart === 'undefined') return;

    new Chart(chartCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'Acceptees', 'Refusees'],
            datasets: [{
                data: [
                    <?= (int)($statsStatut['en_attente'] ?? 0) ?>,
                    <?= (int)($statsStatut['accepte'] ?? 0) ?>,
                    <?= (int)($statsStatut['refuse'] ?? 0) ?>
                ],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 62,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 14,
                    padding: 18,
                    fontColor: '#2d3748'
                }
            }
        }
    });
});
</script>
