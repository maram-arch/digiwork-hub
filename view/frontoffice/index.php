<?php

session_start();

require_once __DIR__ . '/../../controller/UserController.php';

$action = $_GET['action'] ?? '';

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
            $_SESSION['user'] = $result['user'];
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

include __DIR__ . '/index.html';
