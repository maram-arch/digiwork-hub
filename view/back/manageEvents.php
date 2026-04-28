<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$listEvents = $eventController->listEvents();
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
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px 40px; }
        .section-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; color: var(--text-dark); display: flex; justify-content: space-between; align-items: center; }
        .table-wrapper { overflow-x: auto; }
        .event-table { width: 100%; min-width: 1200px; border-collapse: collapse; background-color: var(--white); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 16px; overflow: hidden; }
        .event-table th, .event-table td { padding: 16px 18px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 14px; color: var(--text-dark); }
        .event-table th { background-color: #f7fafc; font-weight: 700; color: var(--primary-blue); }
        .event-table tbody tr:hover { background-color: #f1f5f9; }
        .event-table td:last-child { white-space: nowrap; }
        .btn-modifier, .btn-supprimer { text-decoration: none; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; padding: 8px 16px; cursor: pointer; transition: background 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .btn-modifier { background-color: var(--primary-green); color: var(--white); margin-right: 10px; }
        .btn-modifier:hover { background-color: #5a9a2f; }
        .btn-supprimer { background-color: #e53e3e; color: var(--white); }
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
                            $dateStr = isset($event['date_event']) ? htmlspecialchars($event['date_event']) : 'À définir';
                            $heureStr = isset($event['heure_event']) ? htmlspecialchars($event['heure_event']) : '---';
                            $titreStr = isset($event['titre']) ? htmlspecialchars($event['titre']) : 'Sans titre';
                            $descStr = isset($event['description']) ? htmlspecialchars($event['description']) : '...';
                            $descShort = strlen($descStr) > 70 ? substr($descStr, 0, 67) . '...' : $descStr;
                            $lieuStr = isset($event['lieu']) ? htmlspecialchars($event['lieu']) : 'En ligne';
                            $capaciteStr = isset($event['capacite']) ? (int)$event['capacite'] : 0;
                            $idEvent = isset($event['id_event']) ? htmlspecialchars($event['id_event']) : '';
                        ?>
                            <tr>
                                <td><?php echo $idEvent; ?></td>
                                <td><?php echo $titreStr; ?></td>
                                <td class="event-desc"><?php echo $descShort; ?></td>
                                <td><?php echo $dateStr; ?></td>
                                <td><?php echo $heureStr; ?></td>
                                <td><?php echo $lieuStr; ?></td>
                                <td><?php echo $capaciteStr; ?></td>
                                <td><?php echo isset($event['nbr_inscri']) ? (int)$event['nbr_inscri'] : 0; ?></td>
                                <td>
                                    <a href="../front/inscription.php?id_event=<?php echo $idEvent; ?>" class="btn-modifier" style="background-color: #2270c1;">Voir l'événement</a>
                                    <a href="editEvent.php?id=<?php echo $idEvent; ?>" class="btn-modifier">Modifier</a>
                                    <button type="button" class="btn-supprimer" onclick="deleteEvent(<?php echo $idEvent; ?>)">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color: var(--text-light); padding: 40px 0;">Aucun événement enregistré pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteEvent(eventId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                window.location.href = 'handleDeleteEvent.php?id=' + eventId;
            }
        }
    </script>
</body>
</html>
