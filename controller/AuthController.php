<?php
session_start();
require_once(__DIR__ . '/../config/config.php');

function redirectTo(string $path): void {
    header('Location: ' . $path);
    exit;
}

function isAdmin(PDO $pdo, int $userId): bool {
    $stmt = $pdo->prepare('SELECT 1 FROM admin WHERE id_user = ? LIMIT 1');
    $stmt->execute([$userId]);
    return (bool)$stmt->fetchColumn();
}

// Login (email + password). Kept backward-compatible with legacy user_id login.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $legacyUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    try {
        $user = null;

        if ($email !== '' && $password !== '') {
            $stmt = $pdo->prepare('SELECT id_user, email FROM `user` WHERE email = ? AND mdp = ? LIMIT 1');
            $stmt->execute([$email, $password]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($legacyUserId > 0) {
            $stmt = $pdo->prepare('SELECT id_user, email FROM `user` WHERE id_user = ? LIMIT 1');
            $stmt->execute([$legacyUserId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user && isset($user['id_user'])) {
            $uid = (int)$user['id_user'];

            $_SESSION['user_id'] = $uid;
            $_SESSION['user_email'] = $user['email'] ?? '';

            if (isAdmin($pdo, $uid)) {
                $_SESSION['role'] = 'admin';
                $_SESSION['user_name'] = 'Admin';
                $_SESSION['flash'] = 'Connexion admin réussie.';
                redirectTo('/back/dashboard_packs.php');
            }

            $_SESSION['role'] = 'user';
            $_SESSION['user_name'] = $_SESSION['user_email'] ?: 'Utilisateur';
            $_SESSION['flash'] = 'Connexion réussie.';
            redirectTo('/frontoffice/index.php');
        }
    } catch (PDOException $e) {
        error_log('Auth login failed: ' . $e->getMessage());
    }

    $_SESSION['flash'] = 'Identifiants invalides.';
    redirectTo('/front/login.php');
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['role']);
    $_SESSION['flash'] = 'Déconnexion réussie.';
    redirectTo('/frontoffice/index.php');
}

// Default: redirect to front
redirectTo('/frontoffice/index.php');

?>
