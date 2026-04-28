<?php
require_once __DIR__ . '/../../controller/InscriptionController.php';

$inscriptionController = new InscriptionController();
$listInscriptions = $inscriptionController->listInscriptions();
$message = '';
if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">✅ L\'inscription a été annulée avec succès.</div>';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>DigiWork HUB - Gérer les inscriptions</title>
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; }
        .navbar { background-color: var(--white); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; font-size: 24px; font-weight: 700; color: var(--primary-blue); text-decoration: none; }
        .logo span { color: var(--primary-green); }
        .nav-links { display: flex; gap: 25px; }
        .nav-links a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: color 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--secondary-blue); }
        .page-header { max-width: 1400px; margin: 0 auto; padding: 40px 20px 20px; }
        .section-title { font-size: 32px; margin-bottom: 8px; color: var(--primary-blue); }
        .section-description { color: var(--text-light); font-size: 15px; margin-bottom: 20px; }
        .action-link { display: inline-flex; background-color: var(--primary-green); color: var(--white); padding: 12px 18px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: background 0.3s; }
        .action-link:hover { background-color: var(--primary-green-hover); }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px 40px; }
        .table-wrapper { overflow-x: auto; }
        .event-table { width: 100%; min-width: 1200px; border-collapse: collapse; background-color: var(--white); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 16px; overflow: hidden; }
        .event-table th, .event-table td { padding: 16px 18px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 14px; color: var(--text-dark); }
        .event-table th { background-color: #f7fafc; font-weight: 700; color: var(--primary-blue); }
        .event-table tbody tr:hover { background-color: #f1f5f9; }
        .btn-modifier, .btn-supprimer { text-decoration: none; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; padding: 10px 14px; cursor: pointer; transition: background 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .btn-modifier { background-color: var(--primary-green); color: #ffffff; margin-right: 10px; }
        .btn-modifier:hover { background-color: #5aa131; }
        .btn-supprimer { background-color: #e53e3e; color: white; }
        .btn-supprimer:hover { background-color: #c53030; }
        .table-note { margin-top: 16px; color: var(--text-light); font-size: 14px; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="manageEvents.php" class="logo"><span>DigiWork</span> HUB</a>
        <div class="nav-links">
            <a href="manageEvents.php">Événements</a>
            <a href="manageInscriptions.php" class="active">Inscriptions</a>
        </div>
    </div>

    <div class="page-header">
        <div class="section-title">Gérer les inscriptions</div>
        <div class="section-description">Toutes les inscriptions enregistrées dans la base de données s'affichent ici.</div>
        <a href="manageEvents.php" class="action-link">Retour aux événements</a>
    </div>

    <?php if ($message): ?>
        <div class="container"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="table-wrapper">
            <?php if (count($listInscriptions) > 0): ?>
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>ID inscription</th>
                            <th>Utilisateur</th>
                            <th>Événement</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listInscriptions as $inscription): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inscription['id_inscription']); ?></td>
                                <td><?php echo htmlspecialchars($inscription['user_email'] ?? $inscription['id_user']); ?></td>
                                <td><?php echo htmlspecialchars($inscription['event_title'] ?? $inscription['id_event']); ?></td>
                                <td><?php echo htmlspecialchars($inscription['date_inscription']); ?></td>
                                <td><?php echo htmlspecialchars($inscription['statut']); ?></td>
                                <td>
                                    <a href="editInscription.php?id=<?php echo htmlspecialchars($inscription['id_inscription']); ?>" class="btn-modifier" style="margin-right: 10px;">Modifier</a>
                                    <button type="button" class="btn-supprimer" onclick="deleteInscription(<?php echo htmlspecialchars($inscription['id_inscription']); ?>)">Annuler</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="table-note">Si l'ID utilisateur est invalide ou absent de la table <code>user</code>, il apparaîtra néanmoins ici tant que l'enregistrement existe.</p>
            <?php else: ?>
                <p style="text-align:center; color: var(--text-light); padding: 40px 0;">Aucune inscription enregistrée pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteInscription(inscriptionId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette inscription ?')) {
                window.location.href = 'handleDeleteInscription.php?id=' + inscriptionId;
            }
        }
    </script>
</body>
</html>
