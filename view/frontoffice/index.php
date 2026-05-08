<?php

session_start();

require_once __DIR__ . '/../../controller/UserController.php';

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    if (isset($_SESSION['user_id'])) {
        try {
            (new UserController())->logoutUser((int) $_SESSION['user_id']);
        } catch (Throwable $e) {
        }
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

if (in_array($action, ['login', 'signup', 'verify_otp', 'resend_otp', 'forgot_password', 'verify_reset_otp', 'resend_reset_otp', 'reset_password'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $newUserId = (int) $result['user']['id'];
            $oldUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            if ($oldUserId > 0 && $oldUserId !== $newUserId) {
                try {
                    $controller->markUserOffline($oldUserId);
                } catch (Throwable $e) {
                }
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['front_auth'] = true;
            $controller->touchUserSession($newUserId);
            $result['redirect'] = '../backoffice/index.php';
        }
        echo json_encode($result);
    } elseif ($action === 'verify_otp') {
        $result = $controller->verifyOtp($payload);
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int) $result['user_id'];
            $_SESSION['front_auth'] = true;
            $controller->touchUserSession((int) $result['user_id']);
            $result['redirect'] = '../backoffice/index.php';
        }
        echo json_encode($result);
    } elseif ($action === 'resend_otp') {
        echo json_encode($controller->resendOtp($payload));
    } elseif ($action === 'forgot_password') {
        echo json_encode($controller->forgotPassword($payload));
    } elseif ($action === 'verify_reset_otp') {
        echo json_encode($controller->verifyResetOtp($payload));
    } elseif ($action === 'resend_reset_otp') {
        echo json_encode($controller->resendResetOtp($payload));
    } elseif ($action === 'reset_password') {
        echo json_encode($controller->resetPassword($payload));
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
    'email' => (string) ($currentUser['email'] ?? ''),
];

echo '<script>window.__FRONT_AUTH_STATE__ = ' . json_encode($frontAuthState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>';

include __DIR__ . '/index.html';
