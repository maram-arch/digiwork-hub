<?php

class SmsService
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct()
    {
        $configFile = __DIR__ . '/../config/sms.php';
        if (!file_exists($configFile)) {
            throw new RuntimeException('Fichier config/sms.php introuvable. Copiez config/sms.example.php et renseignez vos credentials Twilio.');
        }
        require_once $configFile;
        if (empty(TWILIO_ACCOUNT_SID) || empty(TWILIO_AUTH_TOKEN) || empty(TWILIO_FROM_NUMBER)) {
            throw new RuntimeException('Credentials Twilio manquants dans config/sms.php.');
        }
        $this->accountSid = TWILIO_ACCOUNT_SID;
        $this->authToken  = TWILIO_AUTH_TOKEN;
        $this->fromNumber = TWILIO_FROM_NUMBER;
    }

    /**
     * Envoie un SMS via l'API REST Twilio.
     *
     * @param string $to   Numéro de destination au format E.164 (ex: +21612345678)
     * @param string $body Corps du message SMS
     * @return bool        true si HTTP 2xx, false sinon (erreur réseau ou réponse non-2xx)
     */
    public function sendSms(string $to, string $body): bool
    {
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $this->accountSid . '/Messages.json';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => $this->accountSid . ':' . $this->authToken,
            CURLOPT_POSTFIELDS     => http_build_query(['To' => $to, 'From' => $this->fromNumber, 'Body' => $body]),
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return false;
        }

        return $httpCode >= 200 && $httpCode < 300;
    }
}
