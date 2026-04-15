<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event'])) {
    
    $titre = isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : '';
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $date_event = isset($_POST['date_event']) ? htmlspecialchars($_POST['date_event']) : '';
    $heure_event = isset($_POST['heure_event']) ? htmlspecialchars($_POST['heure_event']) : '';
    $lieu = isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : '';
    $capacite = isset($_POST['capacite']) ? (int)$_POST['capacite'] : 0;
    $id_organisateur = isset($_POST['id_organisateur']) ? (int)$_POST['id_organisateur'] : null;

    // Vérification: l'organisateur existe-t-il ?
    if ($eventController->checkOrganizerExists($id_organisateur)) {
        $message = '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-weight: 500; text-align:center;">❌ Erreur: L\'ID Organisateur '.$id_organisateur.' existe déjà dans la base de données. Chaque organisateur ne peut créer qu\'un seul événement.</div>';
    } else {
        $event = new Event(
            null, // id_event est Auto-Increment en DB
            $titre,
            $description,
            $date_event,
            $heure_event,
            $lieu,
            $capacite,
            $id_organisateur,
            date('Y-m-d H:i:s')
        );

        $eventController->addEvent($event);

        $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">✅ L\'événement a été enregistré avec succès dans la base de données !</div>';
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>DigiWork HUB - Ajouter un Événement</title>
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
        
        .btn-submit {
            width: 100%; padding: 14px; background-color: var(--primary-green); color: var(--white); border: none;
            border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s, transform 0.1s; margin-top: 15px;
        }
        .btn-submit:hover { background-color: var(--primary-green-hover); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        
        .back-link {
            display: inline-flex; align-items: center; gap: 5px; justify-content: center; width: 100%; margin-top: 20px;
            color: var(--text-light); text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.3s;
        }
        .back-link:hover { color: var(--secondary-blue); }

        .btn-modifier {
            background-color: var(--primary-green);
            color: var(--white);
            text-decoration: none;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            cursor: pointer;
            margin-left: 5px;
        }

        .btn-modifier:hover {
            background-color: #5a9a2f;
        }

        .btn-supprimer {
            background-color: #e53e3e;
            color: var(--white);
            text-decoration: none;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            cursor: pointer;
            margin-left: 5px;
        }

        .btn-supprimer:hover {
            background-color: #c53030;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--text-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-bottom: 50px;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 16px;
            overflow: hidden;
        }

        .event-table th,
        .event-table td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
            color: var(--text-dark);
        }

        .event-table th {
            background-color: #f7fafc;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .event-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .event-table td:last-child {
            white-space: nowrap;
        }

        .event-table .event-actions {
            justify-content: flex-start;
            gap: 10px;
            margin: 0;
        }

        .event-content {
            padding: 20px;
        }

        .event-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary-blue);
        }

        .event-desc {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.5;
            height: 42px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }

        .event-location {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>

    <div class="page-content">
        <div class="form-container">
            <div class="form-header">
                <h2>Créer un Événement</h2>
                <p>Ajoutez un nouvel événement à la plateforme</p>
            </div>
            
            <!-- Error container for JS validation -->
            <div id="js-error" style="display: none; background-color: #fef2f2; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5; font-size: 14px; font-weight: 500;"></div>

            <?php echo $message; ?>

            <form id="ajouterEventForm" action="" method="POST">
                
                <div class="form-group">
                    <label for="titre">Titre (titre)</label>
                    <input type="text" id="titre" name="titre" placeholder="Ex: Atelier Web React">
                </div>

                <div class="form-group">
                    <label for="description">Description (description)</label>
                    <textarea id="description" name="description" placeholder="Description de l'événement..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_day">Date (date_event)</label>
                        <div style="display:flex; gap:10px;">
                            <select id="date_day" name="date_day">
                                <option value="">Jour</option>
                                <?php for ($d = 1; $d <= 31; $d++): $dayValue = str_pad($d, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $dayValue; ?>"><?php echo $dayValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="date_month" name="date_month">
                                <option value="">Mois</option>
                                <?php for ($m = 1; $m <= 12; $m++): $monthValue = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $monthValue; ?>"><?php echo $monthValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="date_year" name="date_year">
                                <option value="">Année</option>
                                <?php for ($y = date('Y'); $y <= date('Y') + 5; $y++): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="date_event" name="date_event">
                    </div>
                    <div class="form-group">
                        <label for="heure_hour">Heure (heure_event)</label>
                        <div style="display:flex; gap:10px;">
                            <select id="heure_hour" name="heure_hour">
                                <option value="">Heure</option>
                                <?php for ($h = 0; $h <= 23; $h++): $hourValue = str_pad($h, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?php echo $hourValue; ?>"><?php echo $hourValue; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="heure_minute" name="heure_minute">
                                <option value="">Minute</option>
                                <?php foreach (['00','15','30','45'] as $min): ?>
                                    <option value="<?php echo $min; ?>"><?php echo $min; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" id="heure_event" name="heure_event">
                    </div>
                </div>

                <div class="form-group">
                    <label for="lieu">Lieu (lieu)</label>
                    <input type="text" id="lieu" name="lieu" placeholder="Ex: Sousse ou En ligne">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacite">Capacité (capacite)</label>
                        <input type="text" id="capacite" name="capacite" placeholder="Ex: 50">
                    </div>
                    <div class="form-group">
                        <label for="id_organisateur">ID Organisateur (id_organisateur)</label>
                        <input type="text" id="id_organisateur" name="id_organisateur" placeholder="Ex: 1">
                    </div>
                </div>

                <button type="submit" name="submit_event" class="btn-submit">Enregistrer l'événement</button>
            </form>

            <a href="../front/event.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Retour aux événements
            </a>
        </div>
    </div>

    <!-- Events List Section -->
    <div style="padding: 30px 20px; text-align:center;">
        <a href="manageEvents.php" class="btn-submit" style="max-width:260px; margin: 0 auto; display:inline-block;">Gérer les événements</a>
    </div>
    <script>
        document.getElementById('ajouterEventForm').addEventListener('submit', function(e) {
            let titre = document.getElementById('titre').value.trim();
            let dateDay = document.getElementById('date_day').value;
            let dateMonth = document.getElementById('date_month').value;
            let dateYear = document.getElementById('date_year').value;
            let heureHour = document.getElementById('heure_hour').value;
            let heureMinute = document.getElementById('heure_minute').value;
            document.getElementById('date_event').value = dateYear + '-' + dateMonth + '-' + dateDay;
            document.getElementById('heure_event').value = heureHour + ':' + heureMinute;

            let description = document.getElementById('description').value.trim();
            let capacite = document.getElementById('capacite').value.trim();
            let idOrga = document.getElementById('id_organisateur').value.trim();
            
            let errorContainer = document.getElementById('js-error');
            let errors = [];

            // Reset borders
            let fields = ['titre', 'description', 'capacite', 'id_organisateur'];
            fields.forEach(f => document.getElementById(f).style.borderColor = 'var(--border-color)');

            // Titre validation
            if (titre.length < 5) {
                errors.push("❌ Le titre doit contenir au moins 5 caractères.");
                document.getElementById('titre').style.borderColor = 'var(--error-color)';
            }

            // Description validation
            if (description.length < 10) {
                errors.push("❌ La description doit contenir au moins 10 caractères.");
                document.getElementById('description').style.borderColor = 'var(--error-color)';
            }

            // Capacite validation
            if (parseInt(capacite) <= 0 || isNaN(capacite)) {
                errors.push("❌ La capacité doit être un nombre supérieur à 0.");
                document.getElementById('capacite').style.borderColor = 'var(--error-color)';
            }

            // ID Responsable validation (8 digits like inscription)
            let regex8 = /^\d{8}$/;
            if (!regex8.test(idOrga)) {
                errors.push("❌ L'ID Organisateur doit contenir exactement 8 chiffres.");
                document.getElementById('id_organisateur').style.borderColor = 'var(--error-color)';
            }

            if (errors.length > 0) {
                e.preventDefault(); // Annuler l'envoi
                errorContainer.innerHTML = errors.join('<br>');
                errorContainer.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll haut pour voir l'erreur
            } else {
                errorContainer.style.display = 'none';
            }
        });

        // Supprimer la bordure rouge au clic
        document.querySelectorAll('#ajouterEventForm input, #ajouterEventForm textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = 'var(--border-color)';
            });
        });

        function deleteEvent(eventId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                window.location.href = 'handleDeleteEvent.php?id=' + eventId;
            }
        }
    </script>
</body>
</html>
