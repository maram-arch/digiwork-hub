<?php
session_start();
require_once('../config/config.php');

class MailingController {
    
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    // Get all users for mailing
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->query("SELECT id_user, email, tel FROM `user`");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Get users with active abonnements
    public function getActiveSubscribers() {
        try {
            $sql = "SELECT DISTINCT u.id_user, u.email, u.tel, a.`date-fin`, p.`nom-pack`
                    FROM `user` u
                    JOIN `abonnement` a ON u.id_user = a.`id-user`
                    JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                    JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                    WHERE a.status = 'actif' AND a.`date-fin` >= CURDATE()";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Get users with expiring abonnements (within 7 days)
    public function getExpiringSubscribers() {
        try {
            $sql = "SELECT DISTINCT u.id_user, u.email, u.tel, a.`date-fin`, p.`nom-pack`
                    FROM `user` u
                    JOIN `abonnement` a ON u.id_user = a.`id-user`
                    JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                    JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                    WHERE a.status = 'actif' 
                    AND a.`date-fin` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Send email to specific user
    public function sendEmail($to, $subject, $message) {
        $headers = "From: noreply@digiwork.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $htmlMessage = $this->getEmailTemplate($subject, $message);
        
        return mail($to, $subject, $htmlMessage, $headers);
    }
    
    // Send bulk email to multiple users
    public function sendBulkEmail($recipients, $subject, $message) {
        $successCount = 0;
        $failedCount = 0;
        $results = [];
        
        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $personalizedMessage = $this->personalizeMessage($message, $recipient);
            
            if ($this->sendEmail($email, $subject, $personalizedMessage)) {
                $successCount++;
                $results[] = ['email' => $email, 'status' => 'success'];
            } else {
                $failedCount++;
                $results[] = ['email' => $email, 'status' => 'failed'];
            }
        }
        
        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'results' => $results
        ];
    }
    
    // Email template
    private function getEmailTemplate($subject, $message) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #00A651 0%, #008040 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .button { display: inline-block; padding: 12px 24px; background: #00A651; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>DigiWork HUB</h1>
                </div>
                <div class='content'>
                    <h2>$subject</h2>
                    $message
                    <p style='margin-top: 30px;'>
                        <a href='http://localhost:8000/view/front/packs.php' class='button'>Voir nos Packs</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 DigiWork HUB. Tous droits réservés.</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    // Personalize message with user data
    private function personalizeMessage($message, $user) {
        $replacements = [
            '{nom}' => $user['tel'] ?? 'Client',
            '{email}' => $user['email'] ?? '',
            '{pack}' => $user['nom-pack'] ?? 'votre abonnement',
            '{date_fin}' => isset($user['date-fin']) ? date('d/m/Y', strtotime($user['date-fin'])) : ''
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }
        
        return $message;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new MailingController();
    $action = $_POST['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'getUsers':
            echo json_encode(['status' => 'success', 'data' => $controller->getAllUsers()]);
            break;
            
        case 'getActiveSubscribers':
            echo json_encode(['status' => 'success', 'data' => $controller->getActiveSubscribers()]);
            break;
            
        case 'getExpiringSubscribers':
            echo json_encode(['status' => 'success', 'data' => $controller->getExpiringSubscribers()]);
            break;
            
        case 'sendBulk':
            $recipients = json_decode($_POST['recipients'], true);
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            
            if (empty($recipients) || empty($subject) || empty($message)) {
                echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
                break;
            }
            
            $result = $controller->sendBulkEmail($recipients, $subject, $message);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Action non reconnue']);
    }
    exit;
}
?>
