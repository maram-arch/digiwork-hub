<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$listEvents = $eventController->listEvents();

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
    <title>DigiWork HUB - Événements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1b4379;
            --secondary-blue: #2270c1;
            --primary-green: #69b83b;
            --bg-color: #f7f9fc;
            --text-dark: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
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

        .nav-links a.active {
            border-bottom: 2px solid var(--secondary-blue);
            padding-bottom: 5px;
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            color: var(--white);
            text-align: center;
            padding: 60px 20px;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Container */
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

        /* Event Cards */
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

        /* Mock images for events */
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

        .btn-inscrire {
            background-color: var(--primary-blue);
            color: var(--white);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-inscrire:hover {
            background-color: var(--secondary-blue);
        }

        /* Search Bar */
        .search-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: var(--white);
        }

        .search-input-wrapper input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(34, 112, 193, 0.1);
        }

        .search-input-wrapper input::placeholder {
            color: var(--text-light);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .btn-view-all {
            background-color: var(--secondary-blue);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .btn-view-all:hover {
            background-color: var(--primary-blue);
        }

        .no-results {
            text-align: center;
            grid-column: 1 / -1;
            color: var(--text-light);
            padding: 50px 0;
            font-size: 16px;
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

    <section class="hero">
        <h1>Découvrez Nos Événements</h1>
        <p>Participez à nos événements, formations et ateliers pour booster votre carrière digitale.</p>
    </section>

    <div class="container">
        <div class="section-title">
            Événements Recommandés pour Vous
        </div>

        <!-- Search Bar Section -->
        <div class="search-section">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="searchInput" placeholder="Rechercher un événement (titre, lieu, description)...">
            </div>
            <button class="btn-view-all" onclick="viewAllEvents()">Voir tout</button>
        </div>

        <div class="event-grid" id="eventGrid">
            <?php
            if (count($listEvents) > 0) {
                $i = 0;
                foreach ($listEvents as $event) {
                    $img = $images[$i % count($images)];
                    // Ensure attributes exist
                    $dateStr = isset($event['date_event']) ? htmlspecialchars($event['date_event']) : 'À définir';
                    $titreStr = isset($event['titre']) ? htmlspecialchars($event['titre']) : 'Sans titre';
                    $descStr = isset($event['description']) ? htmlspecialchars($event['description']) : '...';
                    $lieuStr = isset($event['lieu']) ? htmlspecialchars($event['lieu']) : 'En ligne';
                    $idEvent = isset($event['id_event']) ? htmlspecialchars($event['id_event']) : '';
                    
                    echo '
                    <div class="event-card" data-title="'.$titreStr.'" data-lieu="'.$lieuStr.'" data-description="'.$descStr.'">
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
                                <a href="inscription.php?id_event='.$idEvent.'" class="btn-inscrire">S\'inscrire</a>
                            </div>
                        </div>
                    </div>';
                    $i++;
                }
            } else {
                echo '<p style="text-align:center; grid-column: 1 / -1; color: var(--text-light); padding: 50px 0;">Aucun événement actuellement disponible. Restez connectés !</p>';
            }
            ?>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const eventCards = document.querySelectorAll('.event-card');
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            eventCards.forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const lieu = card.dataset.lieu.toLowerCase();
                const description = card.dataset.description.toLowerCase();

                // Search in title, location, and description
                if (title.includes(searchTerm) || lieu.includes(searchTerm) || description.includes(searchTerm) || searchTerm === '') {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show no results message if needed
            const gridContainer = document.getElementById('eventGrid');
            let noResultsMsg = gridContainer.querySelector('.no-results');
            
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('p');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'Aucun événement trouvé. Essayez une autre recherche.';
                    gridContainer.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        });

        // View all button functionality
        function viewAllEvents() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        }
    </script>

</body>
</html>
