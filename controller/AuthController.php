<?php
session_start();
require_once(__DIR__ . '/../config/config.php');

function redirectWithFlash(string $to, string $message): void {
    $_SESSION['flash'] = $message;
    header('Location: ' . $to);
    exit;
}

function isAdminUser(PDO $pdo, int $userId): bool {
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM `admin` WHERE `id_user` = ? LIMIT 1');
        $stmt->execute([$userId]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}

// Login: email + password, then role-based redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? null) === 'login') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        redirectWithFlash('/view/front/login.php', 'Veuillez saisir votre email et votre mot de passe.');
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM `user` WHERE `email` = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        redirectWithFlash('/view/front/login.php', "Erreur lors de l'authentification.");
    }

    if (!$user) {
        redirectWithFlash('/view/front/login.php', 'Email ou mot de passe incorrect.');
    }

    $dbPassword = (string)($user['mdp'] ?? '');
    if ($dbPassword !== $password) {
        redirectWithFlash('/view/front/login.php', 'Email ou mot de passe incorrect.');
    }

    $userId = (int)($user['id_user'] ?? 0);
    if ($userId <= 0) {
        redirectWithFlash('/view/front/login.php', 'Compte invalide.');
    }

    $role = isAdminUser($pdo, $userId) ? 'admin' : 'user';
    $displayName = (string)($user['nom'] ?? '');
    if ($displayName === '') $displayName = (string)($user['name'] ?? '');
    if ($displayName === '') $displayName = $email;

    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $displayName;
    $_SESSION['role'] = $role;

    if ($role === 'admin') {
        redirectWithFlash('/view/back/dashboard_packs.php', 'Connexion admin réussie.');
    }
    redirectWithFlash('/view/front/packs.php', 'Connexion réussie.');
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['role']);
    $_SESSION['flash'] = 'Déconnexion réussie.';
    header('Location: /view/front/login.php');
    exit;
}

// Default: redirect to front
header('Location: /view/front/packs.php');
exit;

?>
