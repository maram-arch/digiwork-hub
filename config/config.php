<?php 
class Config {
    private static $pdo;

    // ✅ Utilisez votre clé Hugging Face (déjà fournie)
    const HUGGINGFACE_API_KEY = "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";
    const TWILIO_ACCOUNT_SID  = "AC4be4c3b975205c1d29453e90fdc7b665";    
    const TWILIO_AUTH_TOKEN   = "892864efd55acbd19bb8336035af70da";
    const GROQ_API_KEY = "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";
    const TWILIO_WHATSAPP_NUMBER = "+14155238886"; 
    

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "digiwork_hub";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
        }
        return self::$pdo;
    }
}
?>