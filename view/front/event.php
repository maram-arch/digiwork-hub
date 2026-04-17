<?php
require_once __DIR__ . '/../../controller/EventController.php';

$eventController = new EventController();
$listEvents = $eventController->listEvents();

$images = [
    '../assets/Leadership flyer _ Sunday.jpg',
    '../assets/Marketing agency Momentum.webp',
    '../assets/Piano Musical Concert Poster Design for social media.jpg',
    '../assets/Premium Vector _ Open mic neon signs style text.jpg',
    '../assets/Pub bans quiz team trio that keep winning.jpg',
    '../assets/Staff_.jpg',
    '../assets/THIS SATURDAY at 7pm, watch @intermiamicf in their….jpg',
    '../assets/We’re bringing the IWP community together for an….jpg'
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
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 32px;
            margin-bottom: 50px;
        }

        .event-card {
            background-color: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .event-img {
            height: 220px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .event-date-badge {
            position: absolute;
            top: 18px;
            right: 18px;
            background: var(--white);
            color: var(--primary-blue);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        }

        .event-content {
            padding: 26px;
        }

        .event-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--primary-blue);
        }

        .event-desc {
            font-size: 15px;
            color: var(--text-light);
            margin-bottom: 22px;
            line-height: 1.7;
            min-height: 54px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
            align-items: center;
            font-size: 14px;
            color: var(--text-light);
        }

        .event-meta svg {
            min-width: 18px;
            min-height: 18px;
        }

        .event-status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 5px 18px rgba(0,0,0,0.15);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .event-status-badge.coming {
            background: #10b981;
        }

        .event-status-badge.past {
            background: #ef4444;
        }

        .event-countdown {
            font-size: 14px;
            font-weight: 700;
            color: var(--secondary-blue);
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
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-inscrire:hover {
            background-color: var(--secondary-blue);
            transform: translateY(-1px);
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
                    $heureStr = isset($event['heure_event']) ? htmlspecialchars($event['heure_event']) : '';
                    if ($heureStr) {
                        $dateStr .= ' à ' . $heureStr;
                    }
                    $titreStr = isset($event['titre']) ? htmlspecialchars($event['titre']) : 'Sans titre';
                    $descStr = isset($event['description']) ? htmlspecialchars($event['description']) : '...';
                    $lieuStr = isset($event['lieu']) ? htmlspecialchars($event['lieu']) : 'En ligne';
                    $idEvent = isset($event['id_event']) ? htmlspecialchars($event['id_event']) : '';
                    $status = 'À venir';
                    $countdown = 'Date non précisée';
                    if (isset($event['date_event'])) {
                        $eventTime = strtotime($event['date_event'] . ' ' . ($event['heure_event'] ?? '00:00'));
                        $current = time();
                        $diff = $eventTime - $current;
                        if ($diff > 0) {
                            $status = 'À venir';
                            $days = floor($diff / 86400);
                            $hours = floor(($diff % 86400) / 3600);
                            $minutes = floor(($diff % 3600) / 60);
                            $countdown = 'Dans ' . ($days > 0 ? $days . 'j ' : '') . sprintf('%02d:%02d', $hours, $minutes);
                        } else {
                            $status = 'Passé';
                            $diff = abs($diff);
                            $days = floor($diff / 86400);
                            $hours = floor(($diff % 86400) / 3600);
                            $minutes = floor(($diff % 3600) / 60);
                            $countdown = 'Il y a ' . ($days > 0 ? $days . 'j ' : '') . sprintf('%02d:%02d', $hours, $minutes);
                        }
                    }
                    echo '
                    <div class="event-card" data-title="'.$titreStr.'" data-lieu="'.$lieuStr.'" data-description="'.$descStr.'">
                        <div class="event-img" style="background-image: url(\''.$img.'\');">
                            <div class="event-status-badge '.($status === 'Passé' ? 'past' : 'coming').'">'.$status.'</div>
                        </div>
                        <div class="event-content">
                            <h3 class="event-title">'.$titreStr.'</h3>
                            <p class="event-desc">'.$descStr.'</p>
                            <div class="event-meta">
                                <span class="event-date-text">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7V3M16 7V3M3 11H21M5 21H19A2 2 0 0 0 21 19V7A2 2 0 0 0 19 5H5A2 2 0 0 0 3 7V19A2 2 0 0 0 5 21Z"></path></svg>
                                    '.$dateStr.'
                                </span>
                            </div>
                            <div class="event-footer">
                                <span class="event-countdown">'.$countdown.'</span>
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
