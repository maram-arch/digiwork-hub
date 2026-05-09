<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/SmsService.php';
require_once __DIR__ . '/../model/OtpModel.php';

class UserController
{
    private const ALLOWED_ROLES = ['condidat', 'admin', 'entreprise', 'sponsor'];

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public static function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function getCurrentUserId(): ?int
    {
        self::ensureSessionStarted();
        $id = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? null;
        return $id !== null ? (int) $id : null;
    }

    public static function isAdmin(): bool
    {
        self::ensureSessionStarted();
        return (string) ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function requireLogin(): void
    {
        self::ensureSessionStarted();
        if (self::getCurrentUserId() === null) {
            if (headers_sent()) {
                // HTML context — can't send headers, show inline error
                echo '<div style="color:red;padding:20px;">Session expirée. <a href="/projectttttttt/view/backoffice/login.php">Se reconnecter</a></div>';
                exit;
            }
            // Check if this is an AJAX/API request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Authentification requise.']);
            } else {
                header('Location: /projectttttttt/view/backoffice/login.php');
            }
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::ensureSessionStarted();
        // If already logged in as admin via the backoffice session, allow through
        if (self::isAdmin()) {
            return;
        }
        self::requireLogin(); // will exit if not logged in
        if (!self::isAdmin()) {
            if (headers_sent()) {
                echo '<div style="color:red;padding:20px;">Accès administrateur requis.</div>';
                exit;
            }
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Acces administrateur requis.']);
            } else {
                header('Location: /projectttttttt/view/backoffice/login.php');
            }
            exit;
        }
    }

    public function listUsers(): array
    {
        return $this->userModel->getAll();
    }

    public function findUser(int $id): ?array
    {
        return $this->userModel->getById($id);
    }

    public function createUser(array $data): array
    {
        $validation = $this->validateUserData($data, false);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        if ($this->userModel->getByEmail($validation['clean']['email'])) {
            return ['success' => false, 'errors' => ['Cet email existe deja.']];
        }

        $passwordHash = password_hash($validation['clean']['password'], PASSWORD_BCRYPT);

        $created = $this->userModel->create(
            $validation['clean']['email'],
            $passwordHash,
            $validation['clean']['role'],
            $validation['clean']['tel']
        );

        return $created
            ? ['success' => true, 'message' => 'Utilisateur ajoute avec succes.']
            : ['success' => false, 'errors' => ['Creation impossible.']];
    }

    public function updateUser(int $id, array $data): array
    {
        $validation = $this->validateUserData($data, true);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $existing = $this->userModel->getByEmail($validation['clean']['email']);
        if ($existing && (int) $existing['id_user'] !== $id) {
            return ['success' => false, 'errors' => ['Cet email est deja utilise par un autre compte.']];
        }

        $currentUser = $this->userModel->getById($id);
        if (!$currentUser) {
            return ['success' => false, 'errors' => ['Utilisateur introuvable.']];
        }

        $password = $validation['clean']['password'] !== ''
            ? password_hash($validation['clean']['password'], PASSWORD_BCRYPT)
            : $currentUser['mdp'];

        $updated = $this->userModel->update(
            $id,
            $validation['clean']['email'],
            $password,
            $validation['clean']['role'],
            $validation['clean']['tel']
        );

        return $updated
            ? ['success' => true, 'message' => 'Utilisateur modifie avec succes.']
            : ['success' => false, 'errors' => ['Mise a jour impossible.']];
    }

    public function deleteUser(int $id): array
    {
        $deleted = $this->userModel->delete($id);
        return $deleted
            ? ['success' => true, 'message' => 'Utilisateur supprime avec succes.']
            : ['success' => false, 'errors' => ['Suppression impossible.']];
    }

    public function login(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));

        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Email et mot de passe obligatoires.'];
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Identifiants invalides.'];
        }

        $storedPassword = (string) ($user['mdp'] ?? '');
        if (!password_verify($password, $storedPassword)) {
            return ['success' => false, 'message' => 'Identifiants invalides.'];
        }

        if (!$this->userModel->isVerified((int) $user['id_user'])) {
            return ['success' => false, 'message' => 'Votre compte n\'est pas encore verifie. Veuillez verifier votre telephone.'];
        }

        $this->userModel->markOnline((int) $user['id_user']);

        return [
            'success' => true,
            'message' => 'Connexion reussie.',
            'role' => $user['role'],
            'user' => [
                'id' => (int) $user['id_user'],
                'email' => $user['email'],
                'role' => $user['role'],
                'tel' => $user['tel'],
            ],
        ];
    }

    public function signup(array $data): array
    {
        $result = $this->createUser([
            'email'    => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
            'role'     => $data['role'] ?? 'condidat',
            'tel'      => $data['tel'] ?? '',
        ]);

        if (!$result['success']) {
            return $result;
        }

        $user = $this->userModel->getByEmail(trim((string) ($data['email'] ?? '')));
        if (!$user) {
            return ['success' => false, 'errors' => ['Erreur lors de la creation du compte.']];
        }

        $userId = (int) $user['id_user'];
        $tel    = (string) ($user['tel'] ?? $data['tel'] ?? '');

        try {
            $otpModel = new OtpModel();
            $code     = $otpModel->createOtp($userId);
            $sms      = new SmsService();
            $sent     = $sms->sendSms('+216' . $tel, 'Votre code de verification DigiWork Hub : ' . $code . '. Valable 10 minutes.');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Compte cree mais envoi SMS impossible : ' . $e->getMessage()];
        }

        if (!$sent) {
            return ['success' => false, 'message' => 'Compte cree mais l\'envoi du SMS a echoue. Contactez le support.'];
        }

        return [
            'success'      => true,
            'requires_otp' => true,
            'user_id'      => $userId,
            'masked_tel'   => $this->maskTel($tel),
            'message'      => 'Compte cree. Veuillez saisir le code recu par SMS.',
        ];
    }

    public function verifyOtp(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $code   = trim((string) ($data['code'] ?? ''));

        if ($userId <= 0 || !preg_match('/^[0-9]{6}$/', $code)) {
            return ['success' => false, 'message' => 'Donnees invalides.'];
        }

        $otpModel = new OtpModel();
        $status   = $otpModel->verifyOtp($userId, $code);

        if ($status === 'valid') {
            $this->userModel->setVerified($userId);
            $user = $this->userModel->getById($userId);
            return [
                'success' => true,
                'message' => 'Compte verifie.',
                'user_id' => $userId,
                'role'    => $user['role'] ?? '',
            ];
        }

        $messages = [
            'expired' => 'Code expire. Veuillez demander un nouveau code.',
            'used'    => 'Ce code a deja ete utilise.',
            'invalid' => 'Code incorrect. Veuillez reessayer.',
        ];

        return ['success' => false, 'message' => $messages[$status] ?? 'Code invalide.'];
    }

    public function resendOtp(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);

        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Utilisateur invalide.'];
        }

        $user = $this->userModel->getById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $otpModel    = new OtpModel();
        $elapsed     = $otpModel->getSecondsSinceLastOtp($userId);

        if ($elapsed !== null && $elapsed < 60) {
            return [
                'success'           => false,
                'remaining_seconds' => 60 - $elapsed,
                'message'           => 'Veuillez attendre avant de renvoyer.',
            ];
        }

        try {
            $code = $otpModel->createOtp($userId);
            $sms  = new SmsService();
            $sent = $sms->sendSms('+216' . $user['tel'], 'Votre nouveau code DigiWork Hub : ' . $code . '. Valable 10 minutes.');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Envoi SMS impossible : ' . $e->getMessage()];
        }

        if (!$sent) {
            return ['success' => false, 'message' => 'Echec de l\'envoi du SMS.'];
        }

        return ['success' => true, 'message' => 'Nouveau code envoye.'];
    }

    public function forgotPassword(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $genericResponse = ['success' => true, 'message' => 'Si un compte existe avec cet email, un SMS a ete envoye.'];

        if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            return ['success' => false, 'message' => 'Email invalide.'];
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user || !(int)($user['is_verified'] ?? 0)) {
            return $genericResponse; // anti-énumération
        }

        $userId = (int) $user['id_user'];
        $tel    = (string) ($user['tel'] ?? '');

        try {
            $otpModel = new OtpModel();
            $code     = $otpModel->createOtp($userId, 'reset');
            $sms      = new SmsService();
            $sent     = $sms->sendSms('+216' . $tel, 'Votre code de reinitialisation DigiWork Hub : ' . $code . '. Valable 10 minutes.');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Envoi SMS impossible. Veuillez reessayer.'];
        }

        if (!$sent) {
            return ['success' => false, 'message' => 'Envoi SMS impossible. Veuillez reessayer.'];
        }

        return [
            'success'    => true,
            'user_id'    => $userId,
            'masked_tel' => $this->maskTel($tel),
        ];
    }

    public function verifyResetOtp(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $code   = trim((string) ($data['code'] ?? ''));

        if ($userId <= 0 || !preg_match('/^[0-9]{6}$/', $code)) {
            return ['success' => false, 'message' => 'Format de code invalide.'];
        }

        $otpModel = new OtpModel();
        $status   = $otpModel->verifyOtp($userId, $code, 'reset');

        if ($status === 'valid') {
            $_SESSION['reset_token'] = [
                'user_id' => $userId,
                'token'   => bin2hex(random_bytes(16)),
                'expires' => time() + 600,
            ];
            return ['success' => true];
        }

        $messages = [
            'expired' => 'Code expire. Veuillez demander un nouveau code.',
            'used'    => 'Ce code a deja ete utilise.',
            'invalid' => 'Code incorrect. Veuillez reessayer.',
        ];
        return ['success' => false, 'message' => $messages[$status] ?? 'Code invalide.'];
    }

    public function resendResetOtp(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        if ($userId <= 0) return ['success' => false, 'message' => 'Utilisateur invalide.'];

        $user = $this->userModel->getById($userId);
        if (!$user) return ['success' => false, 'message' => 'Utilisateur introuvable.'];

        $otpModel = new OtpModel();
        $elapsed  = $otpModel->getSecondsSinceLastOtp($userId, 'reset');

        if ($elapsed !== null && $elapsed < 60) {
            return ['success' => false, 'remaining_seconds' => 60 - $elapsed, 'message' => 'Veuillez attendre avant de renvoyer.'];
        }

        try {
            $code = $otpModel->createOtp($userId, 'reset');
            $sms  = new SmsService();
            $sent = $sms->sendSms('+216' . $user['tel'], 'Votre nouveau code DigiWork Hub : ' . $code . '. Valable 10 minutes.');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Envoi SMS impossible.'];
        }

        if (!$sent) return ['success' => false, 'message' => 'Echec de l\'envoi du SMS.'];
        return ['success' => true, 'message' => 'Nouveau code envoye.'];
    }

    public function resetPassword(array $data): array
    {
        $userId      = (int) ($data['user_id'] ?? 0);
        $newPassword = (string) ($data['new_password'] ?? '');

        // Vérifier le reset_token de session
        $token = $_SESSION['reset_token'] ?? null;
        if (!$token
            || (int)($token['user_id'] ?? 0) !== $userId
            || ($token['expires'] ?? 0) < time()
        ) {
            return ['success' => false, 'message' => 'Session expiree. Veuillez recommencer.'];
        }

        // Valider la politique de mot de passe
        $errors = $this->validatePasswordComplexity($newPassword);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($userId, $hash);

        // Invalider le token
        unset($_SESSION['reset_token']);

        return ['success' => true, 'message' => 'Mot de passe reinitialise avec succes.'];
    }

    public function manualVerify(int $userId): array
    {
        $user = $this->userModel->getById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $this->userModel->setVerified($userId);

        return ['success' => true, 'message' => 'Compte verifie manuellement.'];
    }

    public function getAllowedRoles(): array
    {
        return self::ALLOWED_ROLES;
    }

    public function getOnlineUsers(): array
    {
        return $this->userModel->getOnlineUsers();
    }

    public function touchUserSession(int $userId): void
    {
        $this->userModel->touchActivity($userId);
    }

    public function markUserOffline(int $userId): void
    {
        $this->userModel->markOffline($userId);
    }

    public function logoutUser(int $userId): void
    {
        $this->userModel->markOffline($userId);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookieParams = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
        }

        session_destroy();
    }

    private function maskTel(string $tel): string
    {
        $tel = preg_replace('/[^0-9]/', '', $tel);
        if (strlen($tel) < 2) {
            return '+216 XXXXXXXX';
        }
        $last2 = substr($tel, -2);
        return '+216 XX XXX XX ' . $last2;
    }

    private function validateUserData(array $data, bool $isUpdate): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));
        $role = strtolower(trim((string) ($data['role'] ?? 'condidat')));
        $tel = trim((string) ($data['tel'] ?? ''));

        // Keep backward compatibility between UI label and database enum value.
        if ($role === 'candidat') {
            $role = 'condidat';
        }

        $errors = [];

        if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            $errors[] = 'Email invalide.';
        }

        $passwordComplexityRules = !$isUpdate || $password !== '';
        if ($passwordComplexityRules) {
            $errors = array_merge($errors, $this->validatePasswordComplexity($password));
        }

        if (!preg_match('/^[0-9]{8}$/', $tel)) {
            $errors[] = 'Le numero de telephone doit contenir exactement 8 chiffres.';
        }

        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            $errors[] = 'Role invalide.';
            $role = 'condidat';
        }

        return [
            'errors' => $errors,
            'clean' => [
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'tel' => $tel,
            ],
        ];
    }

    /**
     * Politique forte: longueur, majuscules, minuscules, chiffres, caracteres speciaux (ASCII), pas d'espace.
     *
     * @return list<string>
     */
    private function validatePasswordComplexity(string $password): array
    {
        $errs = [];

        if (preg_match('/\s/', $password) === 1) {
            $errs[] = 'Le mot de passe ne doit pas contenir d\'espaces.';
        }

        $len = strlen($password);
        if ($len < 10) {
            $errs[] = 'Le mot de passe doit contenir au moins 10 caracteres.';
        }

        if (preg_match('/[A-Z]/', $password) !== 1) {
            $errs[] = 'Le mot de passe doit contenir au moins une majuscule (A-Z).';
        }

        if (preg_match('/[a-z]/', $password) !== 1) {
            $errs[] = 'Le mot de passe doit contenir au moins une minuscule (a-z).';
        }

        if (preg_match('/[0-9]/', $password) !== 1) {
            $errs[] = 'Le mot de passe doit contenir au moins un chiffre (0-9).';
        }

        if (strpbrk($password, '!@#$%^&*+-=:,.<>?[]{}()|/~') === false) {
            $errs[] = 'Le mot de passe doit contenir au moins un caractere special (exemple: @ # $ % & * ! ? .).';
        }

        return $errs;
    }
}
