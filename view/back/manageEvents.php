<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$listEvents = $eventController->listEvents();
$stats = $eventController->getEventStatistics();
$message = '';

if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">✅ L\'événement a été supprimé avec succès.</div>';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>DigiWork HUB - Gérer les événements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1b4379;
            --secondary-blue: #2270c1;
            --primary-green: #69b83b;
            --primary-green-hover: #5aa131;
            --bg-color: #f7f9fc;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-color: #e2e8f0;
            --white: #ffffff;
            --error-color: #e53e3e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; }
        .navbar { background-color: var(--white); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; font-size: 24px; font-weight: 700; color: var(--primary-blue); text-decoration: none; }
        .logo span { color: var(--primary-green); }
        .nav-links { display: flex; gap: 25px; }
        .nav-links a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: color 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--secondary-blue); }
        .nav-actions { display: flex; gap: 15px; align-items: center; }
        .nav-actions a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 5px; }
        .page-header { max-width: 1400px; margin: 0 auto; padding: 40px 20px 20px; }
        .page-header h1 { font-size: 32px; margin-bottom: 8px; color: var(--primary-blue); }
        .page-header p { color: var(--text-light); font-size: 15px; margin-bottom: 20px; }
        .page-header .action-link { display: inline-flex; background-color: var(--primary-green); color: var(--white); padding: 12px 18px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: background 0.3s; }
        .page-header .action-link:hover { background-color: var(--primary-green-hover); }
        .container { max-width: 1800px; margin: 0 auto; padding: 0 20px 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 34px; }
        .stat-card { position: relative; background: linear-gradient(180deg, #ffffff 0%, #f1f6ff 100%); border: 1px solid rgba(34,112,193,0.15); border-left-width: 6px; border-radius: 18px; padding: 26px 24px; box-shadow: 0 14px 40px rgba(17, 46, 92, 0.08); transition: transform 0.25s ease, box-shadow 0.25s ease; opacity: 0; transform: translateY(18px); animation: fadeInUp 0.65s ease forwards; }
        .stat-card:nth-child(1) { animation-delay: 0.08s; }
        .stat-card:nth-child(2) { animation-delay: 0.16s; }
        .stat-card:nth-child(3) { animation-delay: 0.24s; }
        .stat-card:nth-child(4) { animation-delay: 0.32s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 20px 50px rgba(17, 46, 92, 0.14); }
        .stat-card--events { border-left-color: #2270c1; }
        .stat-card--registrations { border-left-color: #1b4379; }
        .stat-card--upcoming { border-left-color: #69b83b; }
        .stat-card--popular { border-left-color: #f6ad55; }
        .stat-card h3 { margin-bottom: 12px; font-size: 13px; color: #5a6e8f; text-transform: uppercase; letter-spacing: 0.18em; }
        .stat-card strong { display: block; font-size: 40px; margin-bottom: 8px; color: #17325a; }
        .stat-card small { display: block; color: #7f8fa3; font-size: 13px; }
        .chart-grid { display: flex; justify-content: center; gap: 18px; margin-bottom: 32px; }
        .graph-card { display: flex; flex-direction: column; justify-content: flex-start; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; box-shadow: 0 18px 35px rgba(15, 42, 83, 0.06); transition: transform 0.25s ease, box-shadow 0.25s ease; position: relative; min-height: 520px; width: 100%; max-width: 1180px; }
        .graph-card:hover { transform: translateY(-3px); box-shadow: 0 24px 45px rgba(15, 42, 83, 0.1); }
        .graph-title { font-size: 16px; font-weight: 700; color: #1f3c72; margin-bottom: 12px; }
        .graph-subtitle { font-size: 14px; color: #6b7c99; margin-bottom: 18px; }
        .chart-canvas { display: block; width: 100%; height: 460px; max-height: 520px; }
        .chart-legend { display: grid; gap: 12px; margin-top: 18px; }
        .chart-legend-item { display: flex; align-items: center; gap: 10px; }
        .chart-legend-dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; }
        .chart-legend-label { font-size: 13px; color: #283d66; overflow-wrap: break-word; max-width: 220px; }
        .chart-legend-value { font-size: 13px; color: #6b7c99; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        .section-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; color: var(--text-dark); display: flex; justify-content: space-between; align-items: center; }
        .table-wrapper { overflow-x: auto; }
        .event-table { width: 100%; min-width: 1600px; border-collapse: collapse; background-color: var(--white); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 16px; overflow: hidden; }
        .event-table th, .event-table td { padding: 16px 18px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 14px; color: var(--text-dark); }
        .event-table th { background-color: #f7fafc; font-weight: 700; color: var(--primary-blue); }
        .event-table tbody tr:hover { background-color: #f1f5f9; }
        .event-table td:last-child { white-space: nowrap; }
        .btn-action { text-decoration: none; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; padding: 8px 16px; cursor: pointer; transition: background 0.3s; display: inline-flex; align-items: center; justify-content: center; color: #ffffff; margin-right: 6px; }
        .btn-action:last-child { margin-right: 0; }
        .btn-inscriptions { background-color: #2270c1; }
        .btn-inscriptions:hover { background-color: #1a5a9e; }
        .btn-mails { background-color: #7c3aed; }
        .btn-mails:hover { background-color: #6d28d9; }
        .btn-modifier { background-color: var(--primary-green); }
        .btn-modifier:hover { background-color: #5a9a2f; }
        .btn-supprimer { background-color: #e53e3e; }
        .btn-supprimer:hover { background-color: #c53030; }
        .event-desc { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .message-box { max-width: 1100px; margin: 0 auto 20px; padding: 0 20px; }
    </style>
</head>
<body>

    <div class="page-header">
        <div>
            <div class="section-title">Gérer les événements</div>
            <p class="section-description">Liste des événements et accès rapide aux inscriptions.</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="ajouterEvent.php" class="action-link">Ajouter un événement</a>
            <a href="manageInscriptions.php" class="action-link" style="background-color: #2270c1;">Voir les inscriptions</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message-box"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <?php
            $totalEvents        = max(1, (int)$stats['total_events']);
            $upcomingEvents     = (int)$stats['upcoming_events'];
            $totalRegistrations = (int)$stats['total_registrations'];
            $popularRegistrations = !empty($stats['popular_event']) ? (int)$stats['popular_event']['registrations'] : 0;
        ?>

        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card--events">
                <h3>Total des événements</h3>
                <strong><?php echo isset($stats['total_events']) ? (int)$stats['total_events'] : 0; ?></strong>
                <small>Événements actuellement enregistrés</small>
            </div>
            <div class="stat-card stat-card--registrations">
                <h3>Total des inscriptions</h3>
                <strong><?php echo isset($stats['total_registrations']) ? (int)$stats['total_registrations'] : 0; ?></strong>
                <small>Inscriptions collectées sur tous les événements</small>
            </div>
            <div class="stat-card stat-card--upcoming">
                <h3>Événements à venir</h3>
                <strong><?php echo isset($stats['upcoming_events']) ? (int)$stats['upcoming_events'] : 0; ?></strong>
                <small>Événements programmés après aujourd'hui</small>
            </div>
            <div class="stat-card stat-card--popular">
                <h3>Événement le plus populaire</h3>
                <?php if (!empty($stats['popular_event']) && !empty($stats['popular_event']['titre'])): ?>
                    <strong><?php echo htmlspecialchars($stats['popular_event']['titre']); ?></strong>
                    <small><?php echo (int)$stats['popular_event']['registrations']; ?> inscriptions</small>
                <?php else: ?>
                    <strong>Aucun événement</strong>
                    <small>Aucun événement disponible</small>
                <?php endif; ?>
            </div>
        </div>

        <?php
            $eventsForChart = $listEvents;
            usort($eventsForChart, fn($a, $b) => (int)$b['nbr_inscri'] <=> (int)$a['nbr_inscri']);
            $eventsForChart = array_slice($eventsForChart, 0, 6);
            $chartLabels = array_map(fn($e) => htmlspecialchars(substr($e['titre'] ?? 'Sans titre', 0, 20)), $eventsForChart);
            $chartData   = array_map(fn($e) => (int)$e['nbr_inscri'], $eventsForChart);
            $chartColors = ['#0d6efd','#d63384','#f59f00','#84c225','#6f42c1','#20c997'];
        ?>

        <!-- Chart -->
        <div class="chart-grid">
            <div class="graph-card">
                <div class="graph-title">Top événements par nombre d'inscriptions</div>
                <div class="graph-subtitle">Classement des 6 événements les plus populaires</div>
                <canvas id="eventRegistrationsChart" class="chart-canvas"></canvas>
                <div class="chart-legend">
                    <?php foreach ($chartLabels as $index => $label): ?>
                        <div class="chart-legend-item">
                            <span class="chart-legend-dot" style="background: <?php echo $chartColors[$index]; ?>;"></span>
                            <div>
                                <div class="chart-legend-label"><?php echo $label; ?></div>
                                <div class="chart-legend-value"><?php echo $chartData[$index]; ?> inscriptions</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrapper">
            <?php if (count($listEvents) > 0): ?>
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Lieu</th>
                            <th>Capacité</th>
                            <th>Inscriptions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listEvents as $event):
                            $dateStr  = isset($event['date_event'])  ? htmlspecialchars($event['date_event'])  : 'À définir';
                            $heureStr = isset($event['heure_event']) ? htmlspecialchars($event['heure_event']) : '---';
                            $titreStr = htmlspecialchars($event['titre'] ?? 'Sans titre');
                            $descRaw  = $event['description'] ?? '';
                            $descStr  = $descRaw !== '' ? htmlspecialchars($descRaw) : '...';
                            $descShort= mb_strlen($descStr) > 70 ? mb_substr($descStr, 0, 67) . '...' : $descStr;
                            $lieuStr  = htmlspecialchars($event['lieu'] ?? 'En ligne');
                            $capacite = isset($event['capacite']) ? (int)$event['capacite'] : 0;
                            $idEvent  = isset($event['id_event'])  ? (int)$event['id_event']  : 0;
                        ?>
                            <tr>
                                <td><?php echo $idEvent; ?></td>
                                <td><?php echo $titreStr; ?></td>
                                <td class="event-desc"><?php echo $descShort; ?></td>
                                <td><?php echo $dateStr; ?></td>
                                <td><?php echo $heureStr; ?></td>
                                <td><?php echo $lieuStr; ?></td>
                                <td><?php echo $capacite; ?></td>
                                <td><?php echo isset($event['nbr_inscri']) ? (int)$event['nbr_inscri'] : 0; ?></td>
                                <td>
                                    <!-- Voir les inscriptions -->
                                    <a href="manageInscriptions.php?event_id=<?php echo $idEvent; ?>"
                                       class="btn-action btn-inscriptions">Voir inscriptions</a>

                                    <!-- ✅ Voir les mails reçus pour cet événement -->
                                    <a href="viewMails.php?event_id=<?php echo $idEvent; ?>"
                                       class="btn-action btn-mails">Voir les mails</a>

                                    <!-- Modifier -->
                                    <a href="editEvent.php?id=<?php echo $idEvent; ?>"
                                       class="btn-action btn-modifier">Modifier</a>

                                    <!-- Supprimer -->
                                    <button type="button"
                                            class="btn-action btn-supprimer"
                                            onclick="deleteEvent(<?php echo $idEvent; ?>)">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color: var(--text-light); padding: 40px 0;">
                    Aucun événement enregistré pour le moment.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function deleteEvent(eventId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                window.location.href = 'handleDeleteEvent.php?id=' + eventId;
            }
        }

        const eventLabels = <?php echo json_encode($chartLabels, JSON_HEX_TAG); ?>;
        const eventData   = <?php echo json_encode($chartData,   JSON_HEX_TAG); ?>;
        const eventColors = <?php echo json_encode($chartColors, JSON_HEX_TAG); ?>;

        if (eventLabels.length > 0) {
            const ctx = document.getElementById('eventRegistrationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: eventLabels,
                    datasets: [{
                        label: 'Inscriptions',
                        data: eventData,
                        backgroundColor: eventColors,
                        borderColor: eventColors,
                        borderWidth: 1,
                        borderRadius: 12,
                        maxBarThickness: 46
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#4a5568' } },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#edf2f7' },
                            ticks: {
                                color: '#4a5568',
                                precision: 0,
                                stepSize: 1,
                                callback: v => Number.isInteger(v) ? v : ''
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17,46,92,0.92)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            callbacks: { label: ctx => ctx.parsed.y + ' inscriptions' }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>