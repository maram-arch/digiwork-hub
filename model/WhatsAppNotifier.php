<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class WhatsAppNotifier
{
    private $twilioClient;
    private $twilioWhatsAppNumber;

    public function __construct()
    {
        $this->twilioWhatsAppNumber = Config::TWILIO_WHATSAPP_NUMBER;
        $this->twilioClient = new Client(
            Config::TWILIO_ACCOUNT_SID,
            Config::TWILIO_AUTH_TOKEN
        );
    }

    public function sendWhatsAppMessage($toTelephone, $body)
    {
        if (empty($toTelephone)) {
            error_log("WhatsApp: Téléphone vide");
            return false;
        }
        $to = 'whatsapp:+' . preg_replace('/[^0-9]/', '', $toTelephone);
        $from = 'whatsapp:' . $this->twilioWhatsAppNumber;
        try {
            $message = $this->twilioClient->messages->create($to, [
                'from' => $from,
                'body' => $body
            ]);
            error_log("WhatsApp envoyé à $to, SID: " . $message->sid);
            return $message->sid;
        } catch (TwilioException $e) {
            error_log("Twilio erreur: " . $e->getMessage());
            return false;
        }
    }

    public function notifyOwner($id_publication, $id_auteur, $interaction)
    {
        error_log("DEBUG [WhatsAppNotifier::notifyOwner] appelé pour publication $id_publication, auteur $id_auteur, interaction $interaction");
        $pdo = Config::getConnexion();

        $stmt = $pdo->prepare("SELECT id_user, titre FROM forums WHERE id_publication = ?");
        $stmt->execute([$id_publication]);
        $pub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pub) {
            error_log("notifyOwner: publication $id_publication inexistante");
            return;
        }
        if ($pub['id_user'] == $id_auteur) {
            error_log("notifyOwner: auto-notification ignorée");
            return;
        }

        $stmt = $pdo->prepare("SELECT tel FROM user WHERE id_user = ?");
        $stmt->execute([$pub['id_user']]);
        $tel = $stmt->fetchColumn();
        if (!$tel) {
            error_log("notifyOwner: pas de téléphone pour l'utilisateur " . $pub['id_user']);
            return;
        }

        $messages = [
            'like'    => "🔔 Votre publication « {$pub['titre']} » a reçu un like !",
            'comment' => "💬 Nouveau commentaire sur votre publication « {$pub['titre']} ».",
            'share'   => "📢 Votre publication « {$pub['titre']} » a été partagée !"
        ];
        $message = $messages[$interaction] ?? "📢 Interaction sur votre publication « {$pub['titre']} ».";

        error_log("notifyOwner: tentative d'envoi à $tel pour publication {$pub['titre']}");
        $this->sendWhatsAppMessage($tel, $message);
    }
}
?>