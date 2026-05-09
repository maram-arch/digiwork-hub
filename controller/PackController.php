<?php
require_once '../model/Pack.php';
require_once __DIR__ . '/UserController.php';

class PackController {
    private Pack $pack;

    public function __construct() {
        $this->pack = new Pack();
    }

    public function getAll(): void {
        try {
            $result = $this->pack->getAll();
            $packs = [];
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $packs[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode($packs);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch packs: ' . $e->getMessage()]);
        }
    }

    public function getById(int $id): void {
        try {
            $pack = $this->pack->getById($id);
            header('Content-Type: application/json');
            echo json_encode($pack);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch pack: ' . $e->getMessage()]);
        }
    }
}

// Handle requests
$action = $_GET['action'] ?? '';

$controller = new PackController();

switch ($action) {
    case 'getAll':
        $controller->getAll();
        break;
    case 'getById':
        $id = $_GET['id'] ?? 0;
        $controller->getById((int)$id);
        break;
    default:
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}