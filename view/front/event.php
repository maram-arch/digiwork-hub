<?php
require_once __DIR__ . '/../../controller/EventController.php';
require_once __DIR__ . '/../../controller/InscriptionController.php';

$eventController = new EventController();
$inscriptionController = new InscriptionController();
$emailFeedbackMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event_message'])) {
    $eventId = isset($_POST['id_event']) ? intval($_POST['id_event']) : 0;
    $contactEmail = isset($_POST['contact_email']) ? filter_var(trim($_POST['contact_email']), FILTER_SANITIZE_EMAIL) : '';
    $recipientEmail = isset($_POST['recipient_email']) ? filter_var(trim($_POST['recipient_email']), FILTER_SANITIZE_EMAIL) : '';
    $contactSubject = isset($_POST['contact_subject']) ? htmlspecialchars(trim($_POST['contact_subject'])) : '';
    $contactMessage = isset($_POST['contact_message']) ? htmlspecialchars(trim($_POST['contact_message'])) : '';

    try {
        if ($eventId <= 0) {
            throw new Exception('Événement invalide.');
        }
        if ($contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Veuillez saisir une adresse email valide.');
        }
        if ($recipientEmail !== '' && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Veuillez saisir un email destinataire valide ou laissez vide.');
        }
        if ($contactSubject === '') {
            throw new Exception('Veuillez saisir un sujet.');
        }
        if ($contactMessage === '') {
            throw new Exception('Veuillez saisir un message.');
        }

        $event = $eventController->showEvent($eventId);
        $eventTitle = $event ? htmlspecialchars($event['titre']) : 'Événement inconnu';
        $recipient = $recipientEmail !== '' ? $recipientEmail : 'contact@digiworkhub.com';
        $subject = 'Demande de détails : ' . $eventTitle . ' - ' . $contactSubject;
        $body = "Demande de détails pour l'événement : $eventTitle\n";
        $body .= "ID événement : $eventId\n";
        $body .= "Email de l'expéditeur : $contactEmail\n\n";
        if ($recipientEmail !== '') {
            $body .= "Email destinataire demandé : $recipientEmail\n\n";
        }
        $body .= "Message :\n$contactMessage\n";

        if (!$inscriptionController->sendEventEmail($contactEmail, $recipient, $subject, $body)) {
            throw new Exception('Impossible d\'envoyer l\'email pour le moment.');
        }

        $emailFeedbackMessages[$eventId] = '<div class="feedback success">Message envoyé avec succès. Nous vous répondrons bientôt.</div>';
    } catch (Exception $e) {
        $emailFeedbackMessages[$eventId] = '<div class="feedback error">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

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

        .event-map-preview {
            margin-top: 18px;
            border-radius: 18px;
            overflow: hidden;
            height: 180px;
            min-height: 160px;
            box-shadow: inset 0 0 0 1px rgba(203, 242, 255, 0.9);
        }

        .event-map-preview iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .event-location {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .toggle-details-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 18px;
            padding: 12px 16px;
            border-radius: 12px;
            border: none;
            background: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .toggle-details-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .contact-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            border: none;
            background: #10b981;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .contact-toggle-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .contact-form-panel {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.35s ease, opacity 0.35s ease;
        }

        .contact-form-panel.open {
            opacity: 1;
        }

        .contact-form {
            margin-top: 15px;
            display: grid;
            gap: 12px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }

        .contact-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .contact-form button {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: none;
            background: #2563eb;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
        }

        .contact-form button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .feedback {
            margin-top: 14px;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .feedback.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .feedback.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .contact-note {
            font-size: 13px;
            color: #475569;
            margin-top: 8px;
        }

        .event-details-panel {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.35s ease, opacity 0.35s ease;
        }

        .event-details-panel.open {
            opacity: 1;
        }

        .details-grid {
            display: grid;
            gap: 18px;
            margin-top: 18px;
        }

        .detail-card {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            padding: 18px;
            box-shadow: inset 0 0 0 1px rgba(14, 90, 214, 0.06);
        }

        .detail-card h4 {
            margin-bottom: 12px;
            font-size: 15px;
            color: #1f3c72;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            color: #475569;
            font-size: 14px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row span:last-child {
            font-weight: 700;
            color: #1f3c72;
        }

        .detail-map {
            width: 100%;
            min-height: 200px;
            border: none;
            border-radius: 16px;
            margin-top: 12px;
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
        <a href="index.php" class="logo">
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
                    $titreRaw = isset($event['titre']) ? $event['titre'] : 'Sans titre';
                    $titreStr = htmlspecialchars($titreRaw);
                    $descRaw = isset($event['description']) ? $event['description'] : '...';
                    $descStr = htmlspecialchars($descRaw);
                    $lieuRaw = isset($event['lieu']) ? $event['lieu'] : 'En ligne';
                    $lieuStr = htmlspecialchars($lieuRaw);
                    $capaciteStr = isset($event['capacite']) ? (int)$event['capacite'] : 0;
                    $status = 'À venir';
                    $countdown = 'Date non précisée';
                    $idEvent = isset($event['id_event']) ? htmlspecialchars($event['id_event']) : '';
                    $attrTitle = htmlspecialchars($titreRaw, ENT_QUOTES);
                    $attrDesc = htmlspecialchars($descRaw, ENT_QUOTES);
                    $attrLieu = htmlspecialchars($lieuRaw, ENT_QUOTES);
                    $countRegistered = isset($event['nbr_inscri']) ? (int)$event['nbr_inscri'] : 0;
                    $remainingSeats = $capaciteStr ? max(0, $capaciteStr - $countRegistered) : 'N/A';
                    $fillPercent = $capaciteStr ? round(($countRegistered / $capaciteStr) * 100) : 0;
                    $showMap = !empty($lieuRaw) && strtolower(trim($lieuRaw)) !== 'en ligne';
                    $mapQuery = $showMap ? urlencode($lieuRaw) : '';
                    $calendarUrl = '#';
                    if (!empty($event['date_event']) && !empty($event['heure_event'])) {
                        try {
                            $start = new DateTime($event['date_event'] . ' ' . $event['heure_event']);
                            $end = clone $start;
                            $end->modify('+2 hours');
                            $calendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($titreRaw) . '&details=' . urlencode(strip_tags($descRaw)) . '&location=' . urlencode($lieuRaw) . '&dates=' . $start->format('Ymd\THis') . '/' . $end->format('Ymd\THis');
                        } catch (Exception $e) {
                            $calendarUrl = '#';
                        }
                    }
                    ?>
                    <div class="event-card" data-title="<?php echo $attrTitle; ?>" data-lieu="<?php echo $attrLieu; ?>" data-description="<?php echo $attrDesc; ?>">
                        <div class="event-img" style="background-image: url('<?php echo $img; ?>');">
                            <div class="event-status-badge <?php echo ($status === 'Passé' ? 'past' : 'coming'); ?>"><?php echo $status; ?></div>
                        </div>
                        <div class="event-content">
                            <h3 class="event-title"><?php echo $titreStr; ?></h3>
                            <p class="event-desc"><?php echo $descStr; ?></p>
                            <div class="event-meta">
                                <span class="event-date-text">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7V3M16 7V3M3 11H21M5 21H19A2 2 0 0 0 21 19V7A2 2 0 0 0 19 5H5A2 2 0 0 0 3 7V19A2 2 0 0 0 5 21Z"></path></svg>
                                    <?php echo $dateStr; ?>
                                </span>
                            </div>
                            <?php if ($showMap): ?>
                                <div class="event-map-preview">
                                    <iframe src="https://www.google.com/maps?q=<?php echo $mapQuery; ?>&output=embed" allowfullscreen loading="lazy"></iframe>
                                </div>
                            <?php endif; ?>
                            <div class="event-footer">
                                <span class="event-countdown"><?php echo $countdown; ?></span>
                                <span class="event-location" style="color: var(--primary-blue);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?php echo $lieuStr; ?>
                                </span>
                                <a href="inscription.php?id_event=<?php echo $idEvent; ?>" class="btn-inscrire">S'inscrire</a>
                            </div>
                            <button type="button" class="contact-toggle-btn" onclick="toggleContactForm(<?php echo $idEvent; ?>)">Rédiger ma demande</button>
                            <div class="contact-form-panel" id="contact-form-<?php echo $idEvent; ?>">
                                <form action="" method="POST" class="contact-form">
                                    <input type="hidden" name="id_event" value="<?php echo $idEvent; ?>">
                                    <input type="email" name="contact_email" placeholder="Votre email" required>
                                    <input type="email" name="recipient_email" placeholder="Email destinataire (optionnel)">
                                    <input type="text" name="contact_subject" placeholder="Sujet" required>
                                    <textarea name="contact_message" rows="6" placeholder="Écrivez ici ce que vous souhaitez demander" required></textarea>
                                    <p class="contact-note">Veuillez écrire votre demande avant de cliquer sur Envoyer.</p>
                                    <button type="submit" name="submit_event_message">Envoyer la demande</button>
                                </form>
                            </div>
                            <button type="button" class="toggle-details-btn" onclick="toggleEventDetails(<?php echo $idEvent; ?>)">Voir détails</button>
                            <div class="event-details-panel" id="details-panel-<?php echo $idEvent; ?>">
                                <div class="details-grid">
                                    <div class="detail-card">
                                        <h4>Statut et capacité</h4>
                                        <div class="detail-row"><span>Total invités</span><span><?php echo $countRegistered; ?></span></div>
                                        <div class="detail-row"><span>Capacité</span><span><?php echo $capaciteStr; ?></span></div>
                                        <div class="detail-row"><span>Places restantes</span><span><?php echo $remainingSeats; ?></span></div>
                                        <div class="detail-row"><span>Taux de remplissage</span><span><?php echo $fillPercent; ?>%</span></div>
                                        <div class="detail-row"><span>Ajouter au calendrier</span><span><a class="btn-inscrire" href="<?php echo $calendarUrl; ?>" target="_blank" style="padding: 10px 14px; font-size: 13px;">Ajouter</a></span></div>
                                    </div>
                                    <div class="detail-card">
                                        <h4>Localisation</h4>
                                        <div class="detail-row"><span>Adresse</span><span><?php echo $lieuStr; ?></span></div>
                                        <?php if (!$showMap): ?>
                                            <p style="margin:0; color:#475569;">Événement en ligne ou lieu non précisé.</p>
                                        <?php else: ?>
                                            <p style="margin:0; color:#475569;">La carte est affichée ci-dessus.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-card">
                                        <h4>Demander plus de détails</h4>
                                        <?php if (isset($emailFeedbackMessages[$idEvent])): ?>
                                            <?php echo $emailFeedbackMessages[$idEvent]; ?>
                                        <?php endif; ?>
                                        <p style="margin:0; color:#475569;">Cliquez sur le bouton ci-dessus pour afficher le formulaire et envoyer votre demande directement.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
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

        function toggleEventDetails(eventId) {
            const panel = document.getElementById('details-panel-' + eventId);
            if (!panel) return;
            const isOpen = panel.classList.contains('open');
            document.querySelectorAll('.event-details-panel.open').forEach(openPanel => {
                openPanel.classList.remove('open');
                openPanel.style.maxHeight = '0';
            });
            if (!isOpen) {
                panel.classList.add('open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        }

        window.addEventListener('resize', () => {
            document.querySelectorAll('.event-details-panel.open').forEach(panel => {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            });
        });

        // View all button functionality
        function viewAllEvents() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        }

        function toggleContactForm(eventId) {
            const formPanel = document.getElementById('contact-form-' + eventId);
            if (!formPanel) {
                return;
            }
            const isOpen = formPanel.classList.contains('open');

            document.querySelectorAll('.contact-form-panel.open').forEach(panel => {
                panel.classList.remove('open');
                panel.style.maxHeight = '0';
            });

            if (!isOpen) {
                formPanel.classList.add('open');
                formPanel.style.maxHeight = formPanel.scrollHeight + 'px';
            }
        }

        document.querySelectorAll('.contact-form').forEach(form => {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                const submitButton = form.querySelector('button[type="submit"]');
                const formData = new FormData(form);
                formData.append('ajax', '1');

                submitButton.disabled = true;
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Envoi...';

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    const feedback = form.closest('.detail-card').querySelector('.feedback');

                    if (feedback) {
                        feedback.remove();
                    }

                    const messageBox = document.createElement('div');
                    messageBox.className = 'feedback ' + (data.success ? 'success' : 'error');
                    messageBox.textContent = data.message;
                    form.closest('.detail-card').insertBefore(messageBox, form);

                    if (data.success) {
                        form.reset();
                    }
                } catch (error) {
                    const messageBox = document.createElement('div');
                    messageBox.className = 'feedback error';
                    messageBox.textContent = 'Erreur lors de l\'envoi. Veuillez réessayer.';
                    form.closest('.detail-card').insertBefore(messageBox, form);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        });
    </script>

</body>
</html>
