<?php
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inscription'])) {
    // Les champs correspondent exactement aux colonnes de la Table `inscription`
    $id_user = isset($_POST['id_user']) ? htmlspecialchars($_POST['id_user']) : '';
    $id_event = isset($_POST['id_event']) ? htmlspecialchars($_POST['id_event']) : '';
    $statut = isset($_POST['statut']) ? htmlspecialchars($_POST['statut']) : '';

    $message = '<div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: 500; text-align:center;">Inscription enregistrée pour l\'utilisateur ID : '.$id_user.' (Statut : '.$statut.')</div>';
}

require_once __DIR__ . '/../../controller/EventController.php';

$eventTitle = 'Aucun événement sélectionné';
if (isset($_GET['id_event'])) {
    $eventController = new EventController();
    $event = $eventController->showEvent($_GET['id_event']);
    if ($event) {
        $eventTitle = htmlspecialchars($event['titre']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiWork HUB - Inscription</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background-color: var(--white);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-blue);
            text-decoration: none;
        }

        .logo span {
            color: var(--primary-green);
        }

        .nav-links {
            display: flex;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--secondary-blue);
        }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-actions a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Form Wrapper */
        .page-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(34, 112, 193, 0.05), rgba(105, 184, 59, 0.05));
        }

        .form-container {
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--secondary-blue), var(--primary-green));
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-light);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        input[type="text"], 
        input[type="email"], 
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            color: var(--text-dark);
            transition: all 0.3s;
            background-color: #fdfdfd;
        }

        input[type="text"]:focus, 
        input[type="email"]:focus, 
        select:focus {
            border-color: var(--secondary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 112, 193, 0.1);
            background-color: var(--white);
        }

        input::placeholder {
            color: #a0aec0;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-green);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-1px);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
            width: 100%;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--secondary-blue);
        }

    </style>
</head>
<body>

    <!-- Navbar -->
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
                <h2>Inscription à l'événement</h2>
                <p>Réservez votre place dès maintenant !</p>
            </div>

            <!-- Error container for JS validation -->
            <div id="js-error" style="display: none; background-color: #fef2f2; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5; font-size: 14px; font-weight: 500;"></div>

            <?php echo $message; ?>

            <form id="inscriptionForm" action="" method="POST">
                <div class="form-group">
                    <label for="id_user">ID Utilisateur (id_user)</label>
                    <input type="number" id="id_user" name="id_user" placeholder="Ex: 1" required>
                </div>

                <div class="form-group">
                    <label for="id_event">ID Événement (id_event)</label>
                    <input type="number" id="id_event" name="id_event" placeholder="Ex: 2" value="<?php echo isset($_GET['id_event']) ? htmlspecialchars($_GET['id_event']) : ''; ?>" required>
                </div>

                <div class="event-info" style="background-color: #f0f8ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #b3d9ff;">
                    <strong>Événement sélectionné :</strong> <?php echo $eventTitle; ?>
                </div>

                <div class="form-group">
                    <label for="statut">Statut (statut)</label>
                    <select name="statut" id="statut" required>
                        <option value="En attente">En attente</option>
                        <option value="Confirmé">Confirmé</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>

                <button type="submit" name="submit_inscription" class="btn-submit">Confirmer l'inscription</button>
            </form>

            <a href="event.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Retour aux événements
            </a>
        </div>
    </div>
    <script>
        document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            let idUser = document.getElementById('id_user').value.trim();
            let idEvent = document.getElementById('id_event').value.trim();
            let errorContainer = document.getElementById('js-error');
            let errors = [];

            // Regex for exact 8 digits
            let regex8 = /^\d{8}$/;

            if (!regex8.test(idUser)) {
                errors.push("❌ L'ID Utilisateur doit contenir exactement 8 chiffres.");
            }

            if (!regex8.test(idEvent)) {
                errors.push("❌ L'ID Événement doit contenir exactement 8 chiffres.");
            }

            if (errors.length > 0) {
                e.preventDefault(); // Stop form submission
                errorContainer.innerHTML = errors.join('<br>');
                errorContainer.style.display = 'block';
                // Add red borders to inputs
                if(!regex8.test(idUser)) document.getElementById('id_user').style.borderColor = 'var(--error-color)';
                if(!regex8.test(idEvent)) document.getElementById('id_event').style.borderColor = 'var(--error-color)';
            } else {
                errorContainer.style.display = 'none';
            }
        });

        // Reset borders on input
        document.getElementById('id_user').addEventListener('input', function() {
            this.style.borderColor = 'var(--border-color)';
        });
        document.getElementById('id_event').addEventListener('input', function() {
            this.style.borderColor = 'var(--border-color)';
        });
    </script>
</body>
</html>
