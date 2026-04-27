<?php

session_start();

require_once __DIR__ . '/../../controller/UserController.php';

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    if (isset($_SESSION['user_id'])) {
        try {
            (new UserController())->markUserOffline((int) $_SESSION['user_id']);
        } catch (Throwable $e) {
        }
    }
    unset($_SESSION['front_auth']);
    $_SESSION = [];
    if (session_id() !== '') {
        if (ini_get('session.use_cookies')) {
            $cookieParams = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
        }
        session_destroy();
    }
    header('Location: index.php');
    exit;
}

if ($action === 'heartbeat' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        exit;
    }

    try {
        (new UserController())->touchUserSession((int) $_SESSION['user_id']);
        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false]);
    }
    exit;
}

if (in_array($action, ['login', 'signup'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $controller = new UserController();
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Service temporairement indisponible. Verifiez que MySQL est demarre dans XAMPP.',
        ]);
        exit;
    }

    $payload = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    if ($action === 'login') {
        $result = $controller->login($payload);
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $result['user']['id'];
            $_SESSION['front_auth'] = true;
            $controller->touchUserSession((int) $result['user']['id']);
            $result['redirect'] = $result['user']['role'] === 'admin'
                ? '../backoffice/index.php'
                : 'index.php?connected=1';
        }
        echo json_encode($result);
    } else {
        $result = $controller->signup($payload);
        if (isset($result['errors'])) {
            echo json_encode([
                'success' => false,
                'message' => implode(' ', $result['errors']),
            ]);
        } else {
            echo json_encode($result);
        }
    }
    exit;
}

if (isset($_SESSION['user_id'])) {
    try {
        $currentUser = (new UserController())->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) {
        $currentUser = null;
    }
} else {
    $currentUser = null;
}

$frontAuthState = [
    'loggedIn' => $currentUser !== null,
    'frontAuth' => !empty($_SESSION['front_auth']),
    'userId' => (int) ($currentUser['id_user'] ?? 0),
    'role' => (string) ($currentUser['role'] ?? ''),
];

echo '<script>window.__FRONT_AUTH_STATE__ = ' . json_encode($frontAuthState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>';

include __DIR__ . '/index.html';
