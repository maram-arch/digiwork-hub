<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use Twilio\Rest\Client;

$accountSid = Config::TWILIO_ACCOUNT_SID;
$authToken  = Config::TWILIO_AUTH_TOKEN;
$client = new Client($accountSid, $authToken);

$to = 'whatsapp:+21694213004';          // destinataire (votre numéro)
$from = 'whatsapp:+14155238886';        // numéro Twilio (sandbox)

try {
    $message = $client->messages->create($to, [
        'from' => $from,
        'body' => 'Test depuis DigiWork Hub'
    ]);
    echo "Message envoyé, SID = " . $message->sid;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>