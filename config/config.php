<?php 
class Config {
    private static $pdo;

    // ✅ Utilisez votre clé Hugging Face (déjà fournie)
    const HUGGINGFACE_API_KEY = "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";
    const TWILIO_ACCOUNT_SID  = "AC8247e85c21e08f71bd5139819440d5a2";    
    const TWILIO_AUTH_TOKEN   = "76feee7e5e58257b610788d2e6920a30";      
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