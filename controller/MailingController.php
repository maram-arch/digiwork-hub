<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/UserController.php';

class MailingController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function getAllUsers(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id_user, email, tel FROM `user`");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getActiveSubscribers(): array
    {
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

    public function getExpiringSubscribers(): array
    {
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

    public function sendEmail(string $to, string $subject, string $message): bool
    {
        $headers  = "From: noreply@digiwork.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $this->getEmailTemplate($subject, $message), $headers);
    }

    public function saveEmailHistory(string $subject, string $message, int $recipientCount, int $successCount, int $failedCount): bool
    {
        try {
            // Ensure email_history table exists
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `email_history` (
                `id`               INT(11)      NOT NULL AUTO_INCREMENT,
                `subject`          VARCHAR(255) NOT NULL,
                `message`          TEXT         NOT NULL,
                `recipient_count`  INT(11)      NOT NULL DEFAULT 0,
                `success_count`    INT(11)      NOT NULL DEFAULT 0,
                `failed_count`     INT(11)      NOT NULL DEFAULT 0,
                `sent_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `sent_by`          VARCHAR(50)  DEFAULT 'admin',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $stmt = $this->pdo->prepare(
                "INSERT INTO `email_history` (subject, message, recipient_count, success_count, failed_count, sent_by)
                 VALUES (?, ?, ?, ?, ?, 'admin')"
            );
            return $stmt->execute([$subject, $message, $recipientCount, $successCount, $failedCount]);
        } catch (Exception $e) {
            error_log("Failed to save email history: " . $e->getMessage());
            return false;
        }
    }

    public function getEmailHistory(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM `email_history` ORDER BY sent_at DESC LIMIT 50");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function sendBulkEmail(array $recipients, string $subject, string $message): array
    {
        $successCount = 0;
        $failedCount  = 0;
        $results      = [];

        foreach ($recipients as $recipient) {
            $email               = $recipient['email'];
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
            'failed'  => $failedCount,
            'results' => $results,
        ];
    }

    private function getEmailTemplate(string $subject, string $message): string
    {
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333}
            .container{max-width:600px;margin:0 auto;padding:20px}
            .header{background:linear-gradient(135deg,#00A651 0%,#008040 100%);color:white;padding:20px;text-align:center;border-radius:10px 10px 0 0}
            .content{background:#f9f9f9;padding:30px;border-radius:0 0 10px 10px}
            .footer{text-align:center;margin-top:20px;color:#666;font-size:12px}
            .button{display:inline-block;padding:12px 24px;background:#00A651;color:white;text-decoration:none;border-radius:5px;margin:20px 0}
        </style></head><body>
        <div class='container'>
            <div class='header'><h1>DigiWork HUB</h1></div>
            <div class='content'><h2>{$subject}</h2>{$message}</div>
            <div class='footer'><p>&copy; 2026 DigiWork HUB. Tous droits réservés.</p></div>
        </div></body></html>";
    }

    private function personalizeMessage(string $message, array $user): string
    {
        $replacements = [
            '{nom}'      => $user['tel']       ?? 'Client',
            '{email}'    => $user['email']     ?? '',
            '{pack}'     => $user['nom-pack']  ?? 'votre abonnement',
            '{date_fin}' => isset($user['date-fin'])
                ? date('d/m/Y', strtotime($user['date-fin']))
                : '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
}

// ── Handle AJAX requests ──────────────────────────────────────────────────────
if (basename($_SERVER['PHP_SELF']) === 'MailingController.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    UserController::requireAdmin();
    header('Content-Type: application/json; charset=utf-8');

    $controller = new MailingController();
    $action     = $_POST['action'] ?? '';

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
            $recipients = json_decode($_POST['recipients'] ?? '[]', true);
            $subject    = trim($_POST['subject']    ?? '');
            $message    = trim($_POST['message']    ?? '');
            if (empty($recipients) || $subject === '' || $message === '') {
                echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
                break;
            }
            $result = $controller->sendBulkEmail($recipients, $subject, $message);
            $controller->saveEmailHistory($subject, $message, count($recipients), $result['success'], $result['failed']);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;
        case 'getEmailHistory':
            echo json_encode(['status' => 'success', 'data' => $controller->getEmailHistory()]);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Action non reconnue']);
    }
    exit;
}
