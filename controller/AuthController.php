<?php
session_start();
require_once(__DIR__ . '/../config/config.php');

// Simple auth controller: login by selecting an existing user (no password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user_id = intval($_POST['user_id']);
    if ($user_id > 0) {
        // load user name for convenience
        $stmt = $pdo->prepare('SELECT nom FROM `user` WHERE id_user = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['flash'] = 'Connexion réussie. Bienvenue ' . htmlspecialchars($user['nom']);
            header('Location: /view/front/packs.php');
            exit;
        }
    }

    $_SESSION['flash'] = 'Utilisateur non trouvé.';
    header('Location: /view/front/login.php');
    exit;
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    $_SESSION['flash'] = 'Déconnexion réussie.';
    header('Location: /view/front/packs.php');
    exit;
}

// Default: redirect to front
header('Location: /view/front/packs.php');
exit;

?>
