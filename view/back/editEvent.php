<?php
require_once __DIR__ . '/../../controller/EventController.php';
require_once __DIR__ . '/../../model/Event.php';

$eventController = new EventController();
$event = null;
$message = '';

// Get event data if ID is provided
if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);
    $event = $eventController->showEvent($id);
    
    if (!$event) {
        $message = '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-weight: 500; text-align:center;">Événement non trouvé !</div>';
    }
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $id = htmlspecialchars($_POST['id_event']);
    $titre = isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : '';
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $date_event = isset($_POST['date_event']) ? htmlspecialchars($_POST['date_event']) : '';
    $heure_event = isset($_POST['heure_event']) ? htmlspecialchars($_POST['heure_event']) : '';
    $lieu = isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : '';
    $capacite = isset($_POST['capacite']) ? (int)$_POST['capacite'] : 0;
    $id_organisateur = isset($_POST['id_organisateur']) ? (int)$_POST['id_organisateur'] : null;

    $eventUpdated = new Event(
        null,
        $titre,
        $description,
        $date_event,
        $heure_event,
        $lieu,
        $capacite,
        $id_organisateur
    );

    $eventController->updateEvent($eventUpdated, $id);
    $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">L\'événement a été modifié avec succès !</div>';
    
    // Reload event data
    $event = $eventController->showEvent($id);
}

// Prepare default date and time parts for the form
$date_day = '';
$date_month = '';
$date_year = '';
$heure_hour = '';
$heure_minute = '';
$date_event = '';
$heure_event = '';

if ($event) {
    $date_event = isset($event['date_event']) ? $event['date_event'] : '';
    $heure_event = isset($event['heure_event']) ? $event['heure_event'] : '';

    if ($date_event) {
        $dateParts = explode('-', $date_event);
        if (count($dateParts) === 3) {
            list($date_year, $date_month, $date_day) = $dateParts;
        }
    }

    if ($heure_event) {
        $heureParts = explode(':', $heure_event);
        if (count($heureParts) >= 2) {
            list($heure_hour, $heure_minute) = $heureParts;
        }
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>DigiWork HUB - Modifier Événement</title>
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
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background-color: var(--white); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; font-size: 24px; font-weight: 700; color: var(--primary-blue); text-decoration: none; }
        .logo span { color: var(--primary-green); }
        .nav-links { display: flex; gap: 25px; }
        .nav-links a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: color 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--secondary-blue); }
        .nav-actions { display: flex; gap: 15px; align-items: center; }
        .nav-actions a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 5px; }
        .page-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; background: linear-gradient(135deg, rgba(34, 112, 193, 0.05), rgba(105, 184, 59, 0.05)); }
        
        .form-container {
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 550px;
            position: relative;
        }
        .form-container::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, var(--secondary-blue), var(--primary-green));
            border-top-left-radius: 16px; border-top-right-radius: 16px;
        }
        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header h2 { color: var(--primary-blue); font-weight: 700; font-size: 26px; margin-bottom: 8px; }
        .form-header p { color: var(--text-light); font-size: 14px; }
        
        .form-group { margin-bottom: 15px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 14px; }
        input, textarea, select {
            width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px;
            font-size: 15px; color: var(--text-dark); transition: all 0.3s; background-color: #fdfdfd;
        }
        textarea { resize: vertical; min-height: 80px; }
        input:focus, textarea:focus {
            border-color: var(--secondary-blue); outline: none; box-shadow: 0 0 0 3px rgba(34, 112, 193, 0.1); background-color: var(--white);
        }
        input::placeholder, textarea::placeholder { color: #a0aec0; }
        
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-submit {
            flex: 1;
            padding: 14px; 
            background-color: var(--primary-green); 
            color: var(--white); 
            border: none;
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.3s, transform 0.1s;
        }
        .btn-submit:hover { background-color: var(--primary-green-hover); transform: translateY(-2px); }
        .btn-submit:active { transform: translateY(0); }
        
        .btn-cancel {
            flex: 1;
            padding: 14px; 
            background-color: #cbd5e0; 
            color: var(--text-dark); 
            border: none;
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.3s, transform 0.1s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-cancel:hover { background-color: #a0aec0; transform: translateY(-2px); }
        .btn-cancel:active { transform: translateY(0); }

        .error { color: var(--error-color); font-size: 14px; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="page-content">
        <div class="form-container">
            <?php echo $message; ?>
            
            <?php if ($event): ?>
            <div class="form-header">
                <h2>Modifier Événement</h2>
                <p>Mettez à jour les détails de votre événement</p>
            </div>

            <form method="POST" id="eventForm">
                <input type="hidden" name="id_event" value="<?php echo htmlspecialchars($event['id_event']); ?>">
                
                <div class="form-group">
                    <label for="titre">Titre de l'événement</label>
                    <input type="text" id="titre" name="titre" placeholder="Ex: Atelier Web Design" value="<?php echo htmlspecialchars($event['titre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Décrivez votre événement en détail..." required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lieu">Lieu</label>
                    <input type="text" id="lieu" name="lieu" placeholder="Ex: Salle 101 - Esprit" value="<?php echo htmlspecialchars($event['lieu']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_day">Date</label>
                        <div style="display:flex; gap:10px;">
                            <select id="date_day" name="date_day" required>
                                <option value="">Jour</option>
                                <?php for ($d = 1; $d <= 31; $d++): $dayValue = str_pad($d, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $dayValue; ?>" <?php echo ($dayValue === $date_day) ? 'selected' : ''; ?>><?php echo $dayValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="date_month" name="date_month" required>
                                <option value="">Mois</option>
                                <?php for ($m = 1; $m <= 12; $m++): $monthValue = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $monthValue; ?>" <?php echo ($monthValue === $date_month) ? 'selected' : ''; ?>><?php echo $monthValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="date_year" name="date_year" required>
                                <option value="">Année</option>
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 5; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y === (int)$date_year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="date_event" name="date_event" value="<?php echo htmlspecialchars($date_event); ?>">
                    </div>
                    <div class="form-group">
                        <label for="heure_hour">Heure</label>
                        <div style="display:flex; gap:10px;">
                            <select id="heure_hour" name="heure_hour" required>
                                <option value="">Heure</option>
                                <?php for ($h = 0; $h <= 23; $h++): $hourValue = str_pad($h, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $hourValue; ?>" <?php echo ($hourValue === $heure_hour) ? 'selected' : ''; ?>><?php echo $hourValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="heure_minute" name="heure_minute" required>
                                <option value="">Minute</option>
                                <?php foreach (['00','15','30','45'] as $min): ?>
                                    <option value="<?php echo $min; ?>" <?php echo ($min === $heure_minute) ? 'selected' : ''; ?>><?php echo $min; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" id="heure_event" name="heure_event" value="<?php echo htmlspecialchars($heure_event); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacite">Capacité</label>
                        <input type="text" id="capacite" name="capacite" placeholder="Ex: 50" value="<?php echo (int)$event['capacite']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_organisateur">ID Organisateur</label>
                        <input type="text" id="id_organisateur" name="id_organisateur" placeholder="Ex: 1" value="<?php echo (int)$event['id_organisateur']; ?>">
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" name="update_event" class="btn-submit">Mettre à jour</button>
                    <a href="manageEvents.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
            <?php else: ?>
            <div class="form-header">
                <h2>Erreur</h2>
                <p>Impossible de charger l'événement</p>
            </div>
            <div class="form-buttons">
                <a href="manageEvents.php" class="btn-cancel">Retour aux événements</a>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <script type="text/javascript">
        function setDateTimeHiddenFields() {
            var dateDay = document.getElementById('date_day').value;
            var dateMonth = document.getElementById('date_month').value;
            var dateYear = document.getElementById('date_year').value;
            var heureHour = document.getElementById('heure_hour').value;
            var heureMinute = document.getElementById('heure_minute').value;

            var hiddenDate = document.getElementById('date_event');
            var hiddenHour = document.getElementById('heure_event');

            if (dateDay && dateMonth && dateYear) {
                hiddenDate.value = dateYear + '-' + dateMonth + '-' + dateDay;
            }

            if (heureHour && heureMinute) {
                hiddenHour.value = heureHour + ':' + heureMinute;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setDateTimeHiddenFields();

            var editForm = document.getElementById('eventForm');
            if (editForm) {
                editForm.addEventListener('submit', function(event) {
                    setDateTimeHiddenFields();
                });
            }
        });
    </script>
</body>
</html>
