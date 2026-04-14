<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$message = '';
$listEvents = [];

// Récupérer la liste des événements
$listEvents = $eventController->listEvents();

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
        
        // Rafraîchir la liste des événements
        $listEvents = $eventController->listEvents();
    }
}

$images = [
    'https://images.unsplash.com/photo-1591453089816-0fefbcce48f1?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1633356122544-f134324a6cee?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=600&q=80'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        input[type="text"], input[type="number"], input[type="date"], input[type="time"], textarea {
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

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .event-card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .event-img {
            height: 160px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .event-card:nth-child(1) .event-img {
            background-image: url('https://images.unsplash.com/photo-1591453089816-0fefbcce48f1?auto=format&fit=crop&w=600&q=80');
        }
        
        .event-card:nth-child(2) .event-img {
            background-image: url('https://images.unsplash.com/photo-1633356122544-f134324a6cee?auto=format&fit=crop&w=600&q=80');
        }
        
        .event-card:nth-child(3) .event-img {
            background-image: url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=600&q=80');
        }

        .event-date-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--white);
            color: var(--primary-blue);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

    <nav class="navbar">
        <a href="../home.php" class="logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary-green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            DigiWork <span>HUB</span>
        </a>
        <div class="nav-links">
            <a href="#">Accueil</a>
            <a href="#">Projets</a>
            <a href="#">Formations</a>
            <a href="#">Durabilité</a>
            <a href="event.php" class="active">Événements</a>
        </div>
        <div class="nav-actions">
            <a href="#">Messages</a>
            <a href="#">Profil</a>
        </div>
    </nav>

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
                    <input type="text" id="titre" name="titre" placeholder="Ex: Atelier Web React" required>
                </div>

                <div class="form-group">
                    <label for="description">Description (description)</label>
                    <textarea id="description" name="description" placeholder="Description de l'événement..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_event">Date (date_event)</label>
                        <input type="date" id="date_event" name="date_event" required>
                    </div>
                    <div class="form-group">
                        <label for="heure_event">Heure (heure_event)</label>
                        <input type="time" id="heure_event" name="heure_event" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lieu">Lieu (lieu)</label>
                    <input type="text" id="lieu" name="lieu" placeholder="Ex: Sousse ou En ligne" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacite">Capacité (capacite)</label>
                        <input type="number" id="capacite" name="capacite" placeholder="Ex: 50" required>
                    </div>
                    <div class="form-group">
                        <label for="id_organisateur">ID Organisateur (id_organisateur)</label>
                        <input type="number" id="id_organisateur" name="id_organisateur" placeholder="Ex: 1" required>
                    </div>
                </div>

                <button type="submit" name="submit_event" class="btn-submit">Enregistrer l'événement</button>
            </form>

            <a href="event.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Retour aux événements
            </a>
        </div>
    </div>

    <!-- Events List Section -->
    <div style="background: linear-gradient(135deg, rgba(34, 112, 193, 0.05), rgba(105, 184, 59, 0.05)); padding: 50px 20px;">
        <div class="container">
            <div class="section-title">
                Événements Enregistrés
                <span style="font-size: 14px; color: var(--text-light);">Total: <?php echo count($listEvents); ?></span>
            </div>

            <div class="event-grid">
                <?php
                if (count($listEvents) > 0) {
                    $i = 0;
                    foreach ($listEvents as $event) {
                        $img = $images[$i % count($images)];
                        $dateStr = isset($event['date_event']) ? htmlspecialchars($event['date_event']) : 'À définir';
                        $titreStr = isset($event['titre']) ? htmlspecialchars($event['titre']) : 'Sans titre';
                        $descStr = isset($event['description']) ? htmlspecialchars($event['description']) : '...';
                        $lieuStr = isset($event['lieu']) ? htmlspecialchars($event['lieu']) : 'En ligne';
                        $idEvent = isset($event['id_event']) ? htmlspecialchars($event['id_event']) : '';
                        
                        echo '
                        <div class="event-card">
                            <div class="event-img" style="background-image: url(\''.$img.'\');">
                                <div class="event-date-badge">'.$dateStr.'</div>
                            </div>
                            <div class="event-content">
                                <h3 class="event-title">'.$titreStr.'</h3>
                                <p class="event-desc">'.$descStr.'</p>
                                <div class="event-footer">
                                    <span class="event-location" style="color: var(--primary-blue);">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        '.$lieuStr.'
                                    </span>
                                </div>
                                <div class="event-actions" style="margin-top: 15px;">
                                    <a href="editEvent.php?id='.$idEvent.'" class="btn-modifier">Modifier</a>
                                    <button class="btn-supprimer" onclick="deleteEvent('.$idEvent.')">Supprimer</button>
                                </div>
                            </div>
                        </div>';
                        $i++;
                    }
                } else {
                    echo '<p style="text-align:center; grid-column: 1 / -1; color: var(--text-light); padding: 50px 0;">Aucun événement enregistré pour le moment.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('ajouterEventForm').addEventListener('submit', function(e) {
            let titre = document.getElementById('titre').value.trim();
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
