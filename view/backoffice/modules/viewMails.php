<?php
require_once __DIR__ . '/../../../controller/EventController.php';
require_once __DIR__ . '/../../../config/config.php';

// ══════════════════════════════════════════════════════════════
//  ⚙️  CONFIG SMTP
// ══════════════════════════════════════════════════════════════
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'acadionnews@gmail.com');
define('SMTP_PASS',     'gtgblrmfeopfkppl');
define('SMTP_FROM_NAME','DigiWork HUB');
// ══════════════════════════════════════════════════════════════

$eventController = new EventController();
$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// ─── AJAX reply handler ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_reply'])) {

    ini_set('display_errors', 0);
    error_reporting(0);
    ob_start();

    header('Content-Type: application/json');

    $toEmail      = isset($_POST['to_email'])      ? trim($_POST['to_email'])      : '';
    $replySubject = isset($_POST['reply_subject'])  ? trim($_POST['reply_subject']) : '';
    $replyMessage = isset($_POST['reply_message'])  ? trim($_POST['reply_message']) : '';

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }
    if ($replySubject === '') {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Le sujet est vide.']);
        exit;
    }
    if ($replyMessage === '') {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Le message est vide.']);
        exit;
    }

    $result = sendMailSMTP($toEmail, $replySubject, $replyMessage);

    ob_end_clean();

    if ($result === true) {
        echo json_encode(['success' => true, 'message' => 'Réponse envoyée avec succès à ' . htmlspecialchars($toEmail) . '.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur SMTP : ' . $result]);
    }
    exit;
}

// ─── Fonction envoi SMTP natif ────────────────────────────────────────────────
function sendMailSMTP($to, $subject, $body) {
    $host     = SMTP_HOST;
    $port     = SMTP_PORT;
    $username = SMTP_USER;
    $password = SMTP_PASS;
    $fromName = SMTP_FROM_NAME;
    $from     = $username;

    $errno = 0; $errstr = '';

    // Essai SSL port 465
    $socket = @fsockopen('ssl://' . $host, 465, $errno, $errstr, 10);

    if (!$socket) {
        // Essai STARTTLS port 587
        $socket = @fsockopen($host, 587, $errno, $errstr, 10);
        if (!$socket) {
            return 'Connexion SMTP impossible : ' . $errstr . ' (' . $errno . '). Vérifiez que XAMPP est démarré et que le port n\'est pas bloqué.';
        }
        $useTLS = true;
    } else {
        $useTLS = false;
    }

    $read = fgets($socket, 515);
    if (substr($read, 0, 3) !== '220') {
        fclose($socket);
        return 'Réponse SMTP inattendue : ' . trim($read);
    }

    // EHLO
    fputs($socket, 'EHLO ' . gethostname() . "\r\n");
    while ($line = fgets($socket, 515)) {
        if (substr($line, 3, 1) === ' ') break;
    }

    // STARTTLS si port 587
    if ($useTLS) {
        fputs($socket, "STARTTLS\r\n");
        fgets($socket, 515);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, 'EHLO ' . gethostname() . "\r\n");
        while ($line = fgets($socket, 515)) {
            if (substr($line, 3, 1) === ' ') break;
        }
    }

    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);
    fputs($socket, base64_encode($username) . "\r\n");
    fgets($socket, 515);
    fputs($socket, base64_encode($password) . "\r\n");
    $authResp = fgets($socket, 515);
    if (substr($authResp, 0, 3) !== '235') {
        fclose($socket);
        return 'Authentification SMTP échouée (code : ' . trim($authResp) . '). Vérifiez SMTP_USER / SMTP_PASS — utilisez un App Password Gmail à 16 caractères.';
    }

    // MAIL FROM / RCPT TO / DATA
    fputs($socket, 'MAIL FROM:<' . $from . ">\r\n"); fgets($socket, 515);
    fputs($socket, 'RCPT TO:<'  . $to   . ">\r\n"); fgets($socket, 515);
    fputs($socket, "DATA\r\n"); fgets($socket, 515);

    $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromEncoded    = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>';
    $date           = date('r');

    $message  = 'Date: '    . $date           . "\r\n";
    $message .= 'From: '    . $fromEncoded    . "\r\n";
    $message .= 'To: '      . $to             . "\r\n";
    $message .= 'Subject: ' . $subjectEncoded . "\r\n";
    $message .= 'MIME-Version: 1.0' . "\r\n";
    $message .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $message .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
    $message .= chunk_split(base64_encode($body)) . "\r\n";
    $message .= ".\r\n";

    fputs($socket, $message);
    $sendResp = fgets($socket, 515);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    if (substr($sendResp, 0, 3) !== '250') {
        return 'Envoi échoué : ' . trim($sendResp);
    }

    return true;
}

// ─── Load event info ──────────────────────────────────────────────────────────
$eventInfo = null;
if ($eventId > 0) {
    foreach ($eventController->listEvents() as $e) {
        if ((int)$e['id_event'] === $eventId) { $eventInfo = $e; break; }
    }
}

// ─── Load mails ───────────────────────────────────────────────────────────────
$mails = [];
if ($eventId > 0) {
    try {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("SELECT email, sujet, text FROM mail WHERE `id event` = :eid ORDER BY email ASC");
        $stmt->execute([':eid' => $eventId]);
        $mails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $mails = []; }
}

$titreEvent = $eventInfo
    ? htmlspecialchars($eventInfo['titre'] ?? 'Événement #' . $eventId)
    : 'Événement #' . $eventId;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiWork HUB – Mails de l'événement</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue:         #1b4379;
            --blue2:        #2270c1;
            --purple:       #7c3aed;
            --purple-light: #ede9fe;
            --bg:           #f7f9fc;
            --dark:         #2d3748;
            --light:        #718096;
            --white:        #ffffff;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:var(--bg); color:var(--dark); min-height:100vh; }

        .page-header {
            max-width:1100px; margin:0 auto; padding:40px 20px 20px;
            display:flex; justify-content:space-between;
            align-items:flex-start; flex-wrap:wrap; gap:16px;
        }
        .page-header h1 { font-size:28px; font-weight:700; color:var(--blue); margin-bottom:6px; }
        .page-header p  { font-size:15px; color:var(--light); }

        .btn-back {
            display:inline-flex; align-items:center; gap:8px;
            background:var(--blue2); color:#fff; padding:11px 20px;
            border-radius:10px; text-decoration:none; font-weight:600;
            font-size:14px; transition:background .2s; white-space:nowrap;
        }
        .btn-back:hover { background:var(--blue); }

        .container { max-width:1100px; margin:0 auto; padding:0 20px 60px; }

        .section-title {
            font-size:20px; font-weight:700; color:var(--dark);
            margin-bottom:18px; display:flex; align-items:center; gap:10px;
        }
        .badge {
            background:var(--purple-light); color:#5b21b6;
            border-radius:999px; padding:4px 14px;
            font-size:13px; font-weight:700;
        }

        .mail-list { display:grid; gap:20px; }

        .mail-card {
            background:var(--white); border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,0.07);
            overflow:hidden; transition:box-shadow .25s;
        }
        .mail-card:hover { box-shadow:0 8px 30px rgba(0,0,0,0.11); }

        .mail-card-header {
            display:flex; justify-content:space-between;
            align-items:center; flex-wrap:wrap; gap:12px;
            padding:18px 22px; background:#faf8ff;
            border-bottom:1px solid #e8e3f8;
        }
        .mail-meta { display:flex; flex-direction:column; gap:4px; }
        .mail-from {
            font-size:15px; font-weight:700; color:var(--purple);
            display:flex; align-items:center; gap:8px;
        }
        .mail-subject { font-size:13px; color:var(--light); }

        .btn-reply {
            display:inline-flex; align-items:center; gap:7px;
            background:var(--purple); color:#fff; border:none;
            border-radius:8px; padding:9px 18px; font-size:13px;
            font-weight:700; cursor:pointer;
            transition:background .2s, transform .15s; white-space:nowrap;
        }
        .btn-reply:hover  { background:#6d28d9; transform:translateY(-1px); }
        .btn-reply.active { background:#6d28d9; }

        .mail-body {
            padding:18px 22px; font-size:14px; color:var(--light);
            line-height:1.7; white-space:pre-wrap; word-break:break-word;
            border-bottom:1px solid #f0eeff;
        }

        .reply-panel {
            max-height:0; overflow:hidden; opacity:0;
            transition:max-height .35s ease, opacity .35s ease;
        }
        .reply-panel.open { opacity:1; }

        .reply-form {
            padding:20px 22px; display:grid; gap:14px; background:#f8f5ff;
        }
        .reply-form label {
            font-size:13px; font-weight:600; color:var(--blue);
            margin-bottom:3px; display:block;
        }
        .to-field {
            width:100%; padding:11px 14px; border:1px solid #e2d9f8;
            border-radius:10px; font-size:14px; background:#f0ebff;
            color:var(--purple); font-weight:600;
        }
        .reply-form input[type="text"],
        .reply-form textarea {
            width:100%; padding:11px 14px; border:1px solid #d4c8f8;
            border-radius:10px; font-size:14px; background:#fff;
            transition:border-color .2s, box-shadow .2s;
        }
        .reply-form input[type="text"]:focus,
        .reply-form textarea:focus {
            outline:none; border-color:var(--purple);
            box-shadow:0 0 0 3px rgba(124,58,237,.12);
        }
        .reply-form textarea { min-height:130px; resize:vertical; }

        .btn-send {
            justify-self:end; display:inline-flex; align-items:center; gap:8px;
            background:var(--purple); color:#fff; border:none; border-radius:10px;
            padding:11px 26px; font-size:14px; font-weight:700; cursor:pointer;
            transition:background .2s, transform .15s;
        }
        .btn-send:hover    { background:#6d28d9; transform:translateY(-1px); }
        .btn-send:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        .reply-feedback {
            padding:12px 16px; border-radius:10px;
            font-size:13px; font-weight:600; display:none;
        }
        .reply-feedback.success { background:#dcfce7; color:#166534; border:1px solid #86efac; display:block; }
        .reply-feedback.error   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; display:block; }

        .empty-state { text-align:center; padding:70px 20px; color:var(--light); }
        .empty-state svg { margin-bottom:18px; opacity:.35; }
        .empty-state p { font-size:16px; margin-top:8px; }
    </style>
</head>
<body>

<div class="page-header">
    <div>
        <h1>Mails reçus</h1>
        <p>Événement : <strong><?php echo $titreEvent; ?></strong></p>
    </div>
    <a href="manageEvents.php" class="btn-back">← Retour aux événements</a>
</div>

<div class="container">

<?php if ($eventId <= 0 || $eventInfo === null): ?>
    <div class="empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <p>Événement introuvable.</p>
    </div>

<?php else: ?>

    <div class="section-title">
        Demandes envoyées
        <span class="badge"><?php echo count($mails); ?> mail<?php echo count($mails) !== 1 ? 's' : ''; ?></span>
    </div>

    <?php if (count($mails) > 0): ?>
    <div class="mail-list">
        <?php foreach ($mails as $idx => $mail):
            $safeEmail    = htmlspecialchars($mail['email']);
            $safeSujet    = htmlspecialchars($mail['sujet']);
            $safeMsg      = htmlspecialchars($mail['text']);
            $cardId       = 'card-' . $idx;
            $replySubject = htmlspecialchars('Re: ' . $mail['sujet'], ENT_QUOTES);
            $emailAttr    = htmlspecialchars($mail['email'], ENT_QUOTES);
        ?>
        <div class="mail-card" id="<?php echo $cardId; ?>">

            <div class="mail-card-header">
                <div class="mail-meta">
                    <span class="mail-from">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <?php echo $safeEmail; ?>
                    </span>
                    <span class="mail-subject">Sujet : <?php echo $safeSujet; ?></span>
                </div>
                <button class="btn-reply" onclick="toggleReply('<?php echo $cardId; ?>')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
                    </svg>
                    Répondre
                </button>
            </div>

            <div class="mail-body"><?php echo $safeMsg; ?></div>

            <div class="reply-panel" id="reply-<?php echo $cardId; ?>">
                <div class="reply-form">

                    <div>
                        <label>À :</label>
                        <div class="to-field"><?php echo $safeEmail; ?></div>
                    </div>

                    <div>
                        <label for="subj-<?php echo $cardId; ?>">Sujet :</label>
                        <input type="text"
                               id="subj-<?php echo $cardId; ?>"
                               value="<?php echo $replySubject; ?>">
                    </div>

                    <div>
                        <label for="msg-<?php echo $cardId; ?>">Message :</label>
                        <textarea id="msg-<?php echo $cardId; ?>"
                                  placeholder="Écrivez votre réponse ici…"></textarea>
                    </div>

                    <div class="reply-feedback" id="fb-<?php echo $cardId; ?>"></div>

                    <button class="btn-send"
                            id="send-<?php echo $cardId; ?>"
                            onclick="sendReply('<?php echo $cardId; ?>', '<?php echo $emailAttr; ?>')">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Envoyer la réponse
                    </button>

                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <p>Aucun mail reçu pour cet événement.</p>
        </div>
    <?php endif; ?>

<?php endif; ?>
</div>

<script>
    function toggleReply(cardId) {
        const panel = document.getElementById('reply-' + cardId);
        const btn   = document.querySelector('#' + cardId + ' .btn-reply');
        if (!panel) return;
        const isOpen = panel.classList.contains('open');

        document.querySelectorAll('.reply-panel.open').forEach(p => {
            p.classList.remove('open'); p.style.maxHeight = '0';
        });
        document.querySelectorAll('.btn-reply.active').forEach(b => b.classList.remove('active'));

        if (!isOpen) {
            panel.classList.add('open');
            panel.style.maxHeight = panel.scrollHeight + 'px';
            if (btn) btn.classList.add('active');
            const ta = document.getElementById('msg-' + cardId);
            if (ta) setTimeout(() => ta.focus(), 350);
        }
    }

    document.addEventListener('mouseup', () => {
        document.querySelectorAll('.reply-panel.open').forEach(p => {
            p.style.maxHeight = p.scrollHeight + 'px';
        });
    });

    async function sendReply(cardId, toEmail) {
        const subjEl  = document.getElementById('subj-' + cardId);
        const msgEl   = document.getElementById('msg-'  + cardId);
        const fbEl    = document.getElementById('fb-'   + cardId);
        const sendBtn = document.getElementById('send-' + cardId);
        const panel   = document.getElementById('reply-' + cardId);

        fbEl.className     = 'reply-feedback';
        fbEl.style.display = 'none';
        fbEl.textContent   = '';

        const subject = subjEl.value.trim();
        const message = msgEl.value.trim();

        if (subject === '') { showFeedback(fbEl, false, 'Veuillez saisir un sujet.');   recalcHeight(panel); return; }
        if (message === '') { showFeedback(fbEl, false, 'Veuillez écrire un message.'); recalcHeight(panel); return; }

        sendBtn.disabled    = true;
        sendBtn.textContent = 'Envoi en cours…';

        const formData = new FormData();
        formData.append('ajax_reply',    '1');
        formData.append('to_email',      toEmail);
        formData.append('reply_subject', subject);
        formData.append('reply_message', message);

        const url = window.location.pathname + '?event_id=<?php echo $eventId; ?>';

        try {
            const resp = await fetch(url, { method: 'POST', body: formData });
            const raw  = await resp.text();

            let data;
            try {
                data = JSON.parse(raw);
            } catch(parseErr) {
                showFeedback(fbEl, false, 'Erreur serveur : ' + raw.substring(0, 200));
                recalcHeight(panel);
                return;
            }

            showFeedback(fbEl, data.success, data.message);
            if (data.success) { msgEl.value = ''; }

        } catch (err) {
            showFeedback(fbEl, false, 'Connexion impossible au serveur. Vérifiez que XAMPP est démarré.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Envoyer la réponse`;
            recalcHeight(panel);
        }
    }

    function showFeedback(el, success, msg) {
        el.className     = 'reply-feedback ' + (success ? 'success' : 'error');
        el.style.display = 'block';
        el.textContent   = msg;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function recalcHeight(panel) {
        if (panel && panel.classList.contains('open')) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }
    }
</script>
</body>
</html>