<?php
// view/frontoffice/ai_writing.php
// Handler AJAX — Assistant d'écriture IA (Groq - Gratuit)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = trim($_POST['action'] ?? '');
$texte  = trim($_POST['texte']  ?? '');

if (empty($texte)) {
    echo json_encode(['success' => false, 'message' => 'Texte vide']);
    exit;
}

$prompts = [
    'ameliorer' => "Reformule ce texte en français de façon professionnelle et bienveillante. Réponds UNIQUEMENT avec le texte reformulé, sans introduction ni explication :\n\n" . $texte,
    'corriger'  => "Corrige les fautes d'orthographe et de grammaire de ce texte en français. Réponds UNIQUEMENT avec le texte corrigé :\n\n" . $texte,
    'resumer'   => "Résume ce texte en français en 2 phrases maximum. Réponds UNIQUEMENT avec le résumé :\n\n" . $texte,
    'titre'     => "Génère un titre court et accrocheur en français pour ce texte. Réponds UNIQUEMENT avec le titre, sans guillemets :\n\n" . $texte,
];

if (!array_key_exists($action, $prompts)) {
    echo json_encode(['success' => false, 'message' => 'Action inconnue']);
    exit;
}

$apiKey = Config::GROQ_API_KEY;
$url    = 'https://api.groq.com/openai/v1/chat/completions';

$payload = json_encode([
    'model'       => 'llama-3.1-8b-instant',   // Gratuit, rapide, excellent en français
    'max_tokens'  => 300,
    'temperature' => 0.4,
    'messages'    => [
        [
            'role'    => 'system',
            'content' => 'Tu es un assistant de rédaction en français. Tu réponds UNIQUEMENT avec le résultat demandé, sans introduction, sans explication, sans guillemets.'
        ],
        [
            'role'    => 'user',
            'content' => $prompts[$action]
        ]
    ]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Erreur réseau cURL']);
    exit;
}

$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['choices'][0]['message']['content'])) {
    $result = trim($data['choices'][0]['message']['content']);
    echo json_encode(['success' => true, 'result' => $result]);

} elseif ($httpCode === 401) {
    echo json_encode(['success' => false, 'message' => 'Clé Groq invalide. Vérifie GROQ_API_KEY dans config.php']);

} elseif ($httpCode === 429) {
    echo json_encode(['success' => false, 'message' => 'Limite Groq atteinte. Réessaie dans quelques secondes.']);

} else {
    $errMsg = $data['error']['message'] ?? ('Erreur API HTTP ' . $httpCode);
    echo json_encode(['success' => false, 'message' => $errMsg]);
}