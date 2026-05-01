<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'chatbot.php') {
    header('Content-Type: application/json; charset=utf-8');

    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON invalide']);
        exit;
    }

    $message = trim((string)($payload['message'] ?? ''));
    $page = trim((string)($payload['page'] ?? 'frontoffice'));
    $history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

    if ($message === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Message vide']);
        exit;
    }

    $messageLength = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
    if ($messageLength > 1200) {
        http_response_code(400);
        echo json_encode(['error' => 'Message trop long']);
        exit;
    }

    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        http_response_code(503);
        echo json_encode(['error' => 'OPENAI_API_KEY non configuree']);
        exit;
    }

    if (!function_exists('curl_init')) {
        http_response_code(500);
        echo json_encode(['error' => 'Extension PHP cURL non activee']);
        exit;
    }

    $model = getenv('OPENAI_CHATBOT_MODEL') ?: 'gpt-5.5';
    $systemPrompt = "Tu es l'assistant ChatGPT integre au site DigiWork Hub. "
        . "Reponds toujours en francais clair, concret et utile. "
        . "Evite les reponses vagues. Si l'utilisateur demande 'explique', donne une vraie explication avec exemple. "
        . "Tu peux repondre aux questions generales comme ChatGPT. "
        . "Quand la question concerne DigiWork Hub, aide sur les offres, candidatures, CV, lettres de motivation, recherche, tri, Maps, QR code et statut. "
        . "Ne pretend pas lire la base de donnees si les informations ne sont pas fournies. "
        . "Page actuelle: " . $page . ".";

    $input = [
        [
            'role' => 'system',
            'content' => $systemPrompt,
        ],
    ];

    foreach (array_slice($history, -8) as $entry) {
        $role = ($entry['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
        $content = trim((string)($entry['content'] ?? ''));
        if ($content === '') {
            continue;
        }
        $input[] = [
            'role' => $role,
            'content' => substr($content, 0, 1500),
        ];
    }

    $input[] = [
        'role' => 'user',
        'content' => $message,
    ];

    $request = [
        'model' => $model,
        'input' => $input,
        'max_output_tokens' => 800,
    ];

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($request),
        CURLOPT_TIMEOUT => 35,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['error' => 'Erreur reseau OpenAI: ' . $curlError]);
        exit;
    }

    $data = json_decode($response, true);
    if ($statusCode < 200 || $statusCode >= 300) {
        http_response_code(502);
        $apiError = $data['error']['message'] ?? 'Erreur API OpenAI';
        echo json_encode(['error' => $apiError]);
        exit;
    }

    $answer = $data['output_text'] ?? '';
    if ($answer === '' && !empty($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (($item['type'] ?? '') !== 'message' || empty($item['content'])) {
                continue;
            }
            foreach ($item['content'] as $content) {
                if (isset($content['text'])) {
                    $answer .= $content['text'];
                }
            }
        }
    }

    $answer = trim($answer);
    if ($answer === '') {
        http_response_code(502);
        echo json_encode(['error' => 'Reponse OpenAI vide']);
        exit;
    }

    echo json_encode(['answer' => $answer], JSON_UNESCAPED_UNICODE);
    exit;
}

if (defined('DIGIWORK_CHATBOT_LOADED')) {
    return;
}
define('DIGIWORK_CHATBOT_LOADED', true);
?>
<style>
    .dw-chatbot {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 10050;
        font-family: Arial, sans-serif;
        color: #1e2535;
    }
    .dw-chatbot * { box-sizing: border-box; }
    .dw-chatbot-toggle {
        width: 58px;
        height: 58px;
        border: 0;
        border-radius: 50%;
        background: #435ebe;
        color: #fff;
        box-shadow: 0 14px 34px rgba(67, 94, 190, .32);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform .18s, background .18s;
    }
    .dw-chatbot-toggle:hover {
        background: #3348a8;
        transform: translateY(-2px);
    }
    .dw-chatbot-panel {
        position: absolute;
        right: 0;
        bottom: 76px;
        width: min(360px, calc(100vw - 28px));
        height: 500px;
        max-height: calc(100vh - 110px);
        background: #fff;
        border: 1px solid #e7eaf5;
        border-radius: 16px;
        box-shadow: 0 24px 70px rgba(21, 29, 72, .24);
        overflow: hidden;
        display: none;
        flex-direction: column;
    }
    .dw-chatbot.open .dw-chatbot-panel { display: flex; }
    .dw-chatbot-head {
        background: linear-gradient(135deg, #435ebe, #24358f);
        color: #fff;
        padding: 15px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .dw-chatbot-title {
        font-size: 15px;
        font-weight: 800;
        margin: 0;
        line-height: 1.2;
    }
    .dw-chatbot-subtitle {
        font-size: 12px;
        opacity: .86;
        margin-top: 2px;
    }
    .dw-chatbot-close {
        border: 0;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .16);
        color: #fff;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
    }
    .dw-chatbot-messages {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        background: #f7f9ff;
    }
    .dw-chatbot-msg {
        max-width: 86%;
        margin-bottom: 10px;
        padding: 10px 12px;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.45;
        white-space: pre-line;
        word-wrap: break-word;
    }
    .dw-chatbot-msg.bot {
        background: #fff;
        border: 1px solid #e9edf8;
        color: #2b3448;
        border-top-left-radius: 5px;
    }
    .dw-chatbot-msg.user {
        margin-left: auto;
        background: #435ebe;
        color: #fff;
        border-top-right-radius: 5px;
    }
    .dw-chatbot-suggestions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px 14px 0;
        background: #f7f9ff;
    }
    .dw-chatbot-chip {
        border: 1px solid #dce2f4;
        background: #fff;
        color: #435ebe;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .dw-chatbot-form {
        display: flex;
        gap: 8px;
        padding: 12px;
        border-top: 1px solid #e8ecf6;
        background: #fff;
    }
    .dw-chatbot-input {
        flex: 1;
        min-width: 0;
        border: 1px solid #dce2f4;
        border-radius: 10px;
        padding: 10px 11px;
        font-size: 13px;
        outline: none;
    }
    .dw-chatbot-input:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 3px rgba(67, 94, 190, .12);
    }
    .dw-chatbot-send {
        border: 0;
        border-radius: 10px;
        background: #435ebe;
        color: #fff;
        width: 44px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (max-width: 520px) {
        .dw-chatbot {
            right: 14px;
            bottom: 14px;
        }
        .dw-chatbot-panel {
            bottom: 70px;
            height: min(500px, calc(100vh - 95px));
        }
    }
</style>

<div class="dw-chatbot" id="dwChatbot">
    <div class="dw-chatbot-panel" role="dialog" aria-label="Chatbot DigiWork">
        <div class="dw-chatbot-head">
            <div>
                <p class="dw-chatbot-title">Assistant DigiWork</p>
                <div class="dw-chatbot-subtitle">Posez votre question</div>
            </div>
            <button type="button" class="dw-chatbot-close" id="dwChatbotClose" aria-label="Fermer">&times;</button>
        </div>
        <div class="dw-chatbot-messages" id="dwChatbotMessages"></div>
        <div class="dw-chatbot-suggestions" id="dwChatbotSuggestions">
            <button type="button" class="dw-chatbot-chip">Comment postuler ?</button>
            <button type="button" class="dw-chatbot-chip">Mes candidatures</button>
            <button type="button" class="dw-chatbot-chip">Aide CV</button>
        </div>
        <form class="dw-chatbot-form" id="dwChatbotForm">
            <input class="dw-chatbot-input" id="dwChatbotInput" type="text" autocomplete="off" placeholder="Ecrire un message..." maxlength="600">
            <button class="dw-chatbot-send" type="submit" aria-label="Envoyer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>
    <button type="button" class="dw-chatbot-toggle" id="dwChatbotToggle" aria-label="Ouvrir le chatbot">
        <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
        </svg>
    </button>
</div>

<script>
(function () {
    var root = document.getElementById('dwChatbot');
    if (!root || root.dataset.ready === '1') return;
    root.dataset.ready = '1';

    var toggle = document.getElementById('dwChatbotToggle');
    var closeBtn = document.getElementById('dwChatbotClose');
    var messages = document.getElementById('dwChatbotMessages');
    var form = document.getElementById('dwChatbotForm');
    var input = document.getElementById('dwChatbotInput');
    var suggestions = document.getElementById('dwChatbotSuggestions');
    var pageName = (location.pathname.split('/').pop() || 'index.php').toLowerCase();
    var conversationHistory = [];

    function normalize(text) {
        return (text || '')
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s+\-*/().?,]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function addMessage(text, from) {
        var bubble = document.createElement('div');
        bubble.className = 'dw-chatbot-msg ' + from;
        bubble.textContent = text;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }

    function canCalculate(question) {
        return /^[0-9+\-*/().\s]+$/.test(question) && /[+\-*/]/.test(question);
    }

    function extractCalculation(question) {
        var match = question.match(/-?\d+(?:\.\d+)?(?:\s*[+\-*/]\s*-?\d+(?:\.\d+)?)+/);
        return match ? match[0] : '';
    }

    function calculate(question) {
        try {
            var value = Function('"use strict"; return (' + question + ')')();
            if (Number.isFinite(value)) return question.replace(/\s+/g, '') + ' = ' + value;
        } catch (e) {}
        return null;
    }

    function pageHelp() {
        if (pageName === 'offres.php') {
            return 'Vous etes sur la page des offres. Vous pouvez chercher par titre, competences ou adresse, trier les resultats, ouvrir le detail ou postuler.';
        }
        if (pageName === 'detail_offre.php') {
            return 'Vous etes sur le detail d une offre. Ici vous pouvez lire la description, voir la localisation, scanner le QR code et envoyer votre candidature.';
        }
        if (pageName === 'mes_candidatures.php') {
            return 'Vous etes dans Mes candidatures. Vous pouvez suivre le statut, modifier une candidature en attente, supprimer une candidature ou telecharger le PDF.';
        }
        return 'Vous etes sur DigiWork Hub. Le menu vous permet d aller vers les offres et vos candidatures.';
    }

    function buildAnswer(rawQuestion) {
        var q = normalize(rawQuestion);
        var expression = canCalculate(q) ? q : extractCalculation(q);
        var calc = expression ? calculate(expression) : null;
        if (calc) return calc;

        if (!q) return 'Ecrivez votre question et je vais vous aider.';
        if (q.includes('bonjour') || q.includes('salut') || q.includes('hello') || q.includes('hi')) {
            return 'Bonjour ! Je suis l assistant DigiWork. Vous pouvez me demander de l aide sur les offres, les candidatures, le CV, la lettre de motivation ou une question simple.';
        }
        if (q.includes('maps') || q.includes('map') || q.includes('localisation') || q.includes('adresse') || q.includes('itineraire')) {
            return 'Maps sert a ouvrir la localisation de l offre sur Google Maps.\n\nDans DigiWork Hub, le bouton Maps prend l adresse de l offre et ouvre une carte. Cela aide le candidat a voir ou se trouve le poste, verifier la distance, l itineraire et le temps de trajet.\n\nExemple : si une offre est a Tunis, Maps ouvre directement la recherche de cette adresse sur Google Maps.';
        }
        if (q.includes('qr') || q.includes('qr code') || q.includes('scanner')) {
            return 'Le QR Code sert a ouvrir rapidement le detail de l offre avec un telephone.\n\nLe candidat scanne le code, puis il arrive directement sur la page de l offre sans taper le lien. C est utile pour partager une offre sur une affiche, un document ou un autre ecran.';
        }
        if (q.includes('page') || q.includes('ou suis') || q.includes('aide')) {
            return pageHelp();
        }
        if (q.includes('postuler') || q.includes('candidature') && (q.includes('envoyer') || q.includes('faire'))) {
            return 'Pour postuler : ouvrez une offre, cliquez sur Postuler, ajoutez votre CV en PDF/DOC/DOCX, redigez une lettre de motivation, puis envoyez le formulaire.';
        }
        if (q.includes('mes candidatures') || q.includes('statut') || q.includes('suivre')) {
            return 'Allez dans "Mes candidatures" pour voir vos demandes. Si le statut est en attente, vous pouvez encore modifier ou retirer la candidature.';
        }
        if (q.includes('cv') || q.includes('curriculum')) {
            return 'Pour le CV, utilisez un fichier PDF, DOC ou DOCX. Mettez vos contacts, experiences, competences importantes et adaptez le contenu a l offre.';
        }
        if (q.includes('couleur rouge') || q.includes('rouge')) {
            return 'La couleur rouge peut signifier plusieurs choses selon le contexte.\n\n1. Amour et passion : par exemple les coeurs rouges ou les roses rouges.\n2. Danger ou alerte : un panneau rouge attire l attention et indique souvent qu il faut faire attention.\n3. Interdiction ou erreur : dans les applications, le rouge est souvent utilise pour signaler un probleme.\n4. Energie et force : le rouge donne une impression de puissance, d urgence et d action.\n\nDonc, la couleur rouge n a pas une seule signification. Elle depend de l endroit ou elle est utilisee.';
        }
        if (q.includes('lenovo') || q.includes('ordinateur') || q.includes('pc portable') || q.includes('laptop')) {
            return 'Un PC Lenovo est un ordinateur de la marque Lenovo. Lenovo fabrique plusieurs gammes : ThinkPad pour le travail professionnel, IdeaPad pour les etudes et l usage quotidien, Yoga pour les modeles tactiles ou convertibles, et Legion pour le gaming.\n\nPour choisir un bon PC Lenovo, regardez surtout : le processeur, la RAM, le stockage SSD, l autonomie, la taille de l ecran et l usage voulu. Par exemple, pour bureautique et etudes, 8 Go de RAM et un SSD suffisent souvent. Pour developpement, design ou jeux, 16 Go de RAM ou plus est mieux.';
        }
        if (q.includes('processeur') || q.includes('cpu') || q.includes('ram') || q.includes('ssd')) {
            return 'Dans un PC, le processeur est le cerveau qui execute les calculs. La RAM sert a garder les applications ouvertes rapidement. Le SSD stocke Windows, les fichiers et les programmes, et il rend le PC beaucoup plus rapide qu un ancien disque dur.\n\nPour un usage confortable aujourd hui : SSD obligatoire, 8 Go RAM minimum, 16 Go RAM conseille pour developpement, design ou multitache.';
        }
        if (q.includes('lettre') || q.includes('motivation')) {
            return 'Une bonne lettre explique pourquoi l offre vous interesse, ce que vous apportez, et cite 2 ou 3 competences liees au poste. Restez clair et direct.';
        }
        if (q.includes('recherche') || q.includes('chercher') || q.includes('filtrer')) {
            return 'La recherche permet de trouver une offre plus vite.\n\nVous pouvez chercher par titre, competences ou adresse. Par exemple : "Developpeur", "PHP" ou "Tunis". Le site affiche seulement les offres qui correspondent a votre recherche.';
        }
        if (q.includes('tri') || q.includes('trier') || q.includes('croissant') || q.includes('decroissant')) {
            return 'Le tri sert a organiser les offres.\n\nVous pouvez trier par offre recente, type, date limite ou adresse. Croissant affiche du plus petit au plus grand; decroissant fait l inverse.';
        }
        if (q.includes('offre') || q.includes('emploi') || q.includes('travail') || q.includes('job')) {
            return 'Une offre est une annonce de recrutement. Elle contient generalement le titre du poste, le type de contrat, la description, les competences demandees, l adresse et la date limite.\n\nSur DigiWork Hub, cliquez sur Detail pour lire l offre complete, puis sur Postuler pour envoyer votre CV et votre lettre.';
        }
        if (q.includes('date') || q.includes('aujourd')) {
            return 'Nous sommes le ' + new Date().toLocaleDateString('fr-FR') + '.';
        }
        if (q.includes('heure')) {
            return 'Il est ' + new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) + '.';
        }
        if (q.includes('merci')) {
            return 'Avec plaisir. Posez-moi une autre question quand vous voulez.';
        }
        var topic = rawQuestion
            .replace(/explique\s*moi/ig, '')
            .replace(/c'est quoi/ig, '')
            .replace(/qu'est ce que/ig, '')
            .replace(/que signifie/ig, '')
            .replace(/signifie quoi/ig, '')
            .trim();
        if (topic.length > 2) {
            return topic + ' signifie ou designe quelque chose selon le contexte.\n\nExplication simple : c est une idee, un objet ou un mot dont le sens change parfois selon la situation.\n\nExemple : si vous demandez une couleur, elle peut avoir un sens emotionnel, culturel ou pratique. Si vous demandez un objet, on peut expliquer son role, son utilite et comment il fonctionne.\n\nPour etre plus precis, vous pouvez demander : "explique ' + topic + ' avec un exemple" ou "donne les avantages et inconvenients de ' + topic + '".';
        }
        return 'Je peux vous aider. Posez votre question avec le sujet exact, et je vous repondrai de facon simple et directe.';
    }

    function askOpenAI(question) {
        return fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: question,
                page: pageName,
                history: conversationHistory.slice(-8)
            })
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.error || 'Erreur chatbot');
                }
                return data.answer;
            });
        });
    }

    function sendQuestion(question) {
        addMessage(question, 'user');
        input.value = '';
        input.disabled = true;
        var typing = addMessage('Je reflechis...', 'bot');

        askOpenAI(question)
            .then(function (answer) {
                typing.textContent = answer;
                conversationHistory.push({ role: 'user', content: question });
                conversationHistory.push({ role: 'assistant', content: answer });
                conversationHistory = conversationHistory.slice(-10);
            })
            .catch(function () {
                var fallbackAnswer = buildAnswer(question);
                typing.textContent = fallbackAnswer;
                conversationHistory.push({ role: 'user', content: question });
                conversationHistory.push({ role: 'assistant', content: fallbackAnswer });
                conversationHistory = conversationHistory.slice(-10);
            })
            .finally(function () {
                input.disabled = false;
                input.focus();
                messages.scrollTop = messages.scrollHeight;
            });
    }

    toggle.addEventListener('click', function () {
        root.classList.toggle('open');
        if (root.classList.contains('open')) {
            input.focus();
            if (!messages.dataset.welcome) {
                messages.dataset.welcome = '1';
                addMessage('Bonjour ! Demandez-moi ce que vous voulez sur DigiWork Hub.', 'bot');
            }
        }
    });

    closeBtn.addEventListener('click', function () {
        root.classList.remove('open');
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var question = input.value.trim();
        if (question) sendQuestion(question);
    });

    suggestions.addEventListener('click', function (event) {
        if (event.target.classList.contains('dw-chatbot-chip')) {
            sendQuestion(event.target.textContent);
        }
    });
})();
</script>
