<?php
require_once __DIR__ . '/../../controller/EventController.php';
require_once __DIR__ . '/../../controller/InscriptionController.php';
require_once __DIR__ . '/../../model/Inscription.php';

$inscriptionController = new InscriptionController();
$eventController = new EventController();
$events = $eventController->listEvents();
$message = '';
$inscription = null;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $inscription = $inscriptionController->showInscription((int)$_GET['id']);
    if (!$inscription) {
        $message = '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-weight: 500; text-align:center;">Inscription non trouvée.</div>';
    }
} else {
    $message = '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-weight: 500; text-align:center;">ID d\'inscription manquant ou invalide.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inscription'])) {
    $id = isset($_POST['id_inscription']) ? (int)$_POST['id_inscription'] : 0;
    $id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
    $id_event = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
    $statut = isset($_POST['statut']) ? htmlspecialchars($_POST['statut']) : '';

    if ($id <= 0 || $id_user <= 0 || $id_event <= 0) {
        $message = '<div style="background-color: #fef2f2; color: #b02a37; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c2c7; font-weight: 500; text-align:center;">Veuillez fournir des identifiants valides pour l\'utilisateur, l\'événement et l\'inscription.</div>';
    } elseif (!$eventController->eventExists($id_event)) {
        $message = '<div style="background-color: #fef2f2; color: #b02a37; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c2c7; font-weight: 500; text-align:center;">L\'événement sélectionné n\'existe pas.</div>';
    } else {
        $updatedInscription = new Inscription($id, $id_user, $id_event, null, $statut);
        $inscriptionController->updateInscription($updatedInscription, $id);
        $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">Inscription modifiée avec succès.</div>';
        $inscription = $inscriptionController->showInscription($id);
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>DigiWork HUB - Modifier Inscription</title>
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
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background-color: var(--white); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; font-size: 24px; font-weight: 700; color: var(--primary-blue); text-decoration: none; }
        .logo span { color: var(--primary-green); }
        .nav-links { display: flex; gap: 25px; }
        .nav-links a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: color 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--secondary-blue); }
        .page-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .form-container { background: var(--white); padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 560px; }
        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header h2 { color: var(--primary-blue); font-weight: 700; font-size: 26px; margin-bottom: 8px; }
        .form-header p { color: var(--text-light); font-size: 14px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 14px; }
        input, select { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 15px; color: var(--text-dark); transition: all 0.3s; background-color: #fdfdfd; }
        input:focus, select:focus { border-color: var(--secondary-blue); outline: none; box-shadow: 0 0 0 3px rgba(34,112,193,0.1); background-color: var(--white); }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-submit { flex: 1; padding: 14px; background-color: var(--primary-green); color: var(--white); border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background-color: var(--primary-green-hover); }
        .btn-cancel { flex: 1; display: inline-flex; align-items: center; justify-content: center; padding: 14px; background-color: #cbd5e0; color: var(--text-dark); border: none; border-radius: 8px; font-size: 16px; font-weight: 600; text-decoration: none; }
        .btn-cancel:hover { background-color: #a0aec0; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="manageInscriptions.php" class="logo"><span>DigiWork</span> HUB</a>
        <div class="nav-links">
            <a href="manageEvents.php">Événements</a>
            <a href="manageInscriptions.php" class="active">Inscriptions</a>
        </div>
    </div>

    <div class="page-content">
        <div class="form-container">
            <div class="form-header">
                <h2>Modifier l'inscription</h2>
                <p>Changez l'utilisateur, l'événement ou le statut.</p>
            </div>

            <?php echo $message; ?>

            <?php if ($inscription): ?>
                <form method="POST">
                    <input type="hidden" name="id_inscription" value="<?php echo htmlspecialchars($inscription['id_inscription']); ?>">

                    <div class="form-group">
                        <label for="id_user">ID utilisateur</label>
                        <input type="number" id="id_user" name="id_user" value="<?php echo htmlspecialchars($inscription['id_user']); ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="id_event">Événement</label>
                        <select id="id_event" name="id_event" required>
                            <?php if (empty($events)): ?>
                                <option value="">Aucun événement disponible</option>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo htmlspecialchars($event['id_event']); ?>" <?php echo $event['id_event'] == $inscription['id_event'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['titre'] . ' (ID ' . $event['id_event'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" required>
                            <option value="En attente" <?php echo ($inscription['statut'] === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                            <option value="Confirmé" <?php echo ($inscription['statut'] === 'Confirmé') ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="Annulé" <?php echo ($inscription['statut'] === 'Annulé') ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update_inscription" class="btn-submit">Enregistrer</button>
                        <a href="manageInscriptions.php" class="btn-cancel">Retour</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
