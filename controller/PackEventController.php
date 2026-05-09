<?php
require_once __DIR__ . '/../model/PackEvent.php';
require_once __DIR__ . '/../model/Pack.php';
require_once __DIR__ . '/../model/Event.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/UserController.php';

class PackEventController {
    private PackEvent $packEventModel;

    public function __construct() {
        $this->packEventModel = new PackEvent();
    }

    // Lister toutes les relations pack-événement
    public function getAll(): void {
        $relations = PackEvent::getAll();
        header('Content-Type: application/json');
        echo json_encode($relations);
    }

    // Obtenir les relations par pack
    public function getByPack(): void {
        $id_pack = $_GET['id_pack'] ?? null;
        if (!$id_pack) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pack requis']);
            return;
        }

        $relations = PackEvent::getByPack($id_pack);
        header('Content-Type: application/json');
        echo json_encode($relations);
    }

    // Obtenir les relations par événement
    public function getByEvent(): void {
        $id_event = $_GET['id_event'] ?? null;
        if (!$id_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID événement requis']);
            return;
        }

        $relations = PackEvent::getByEvent($id_event);
        header('Content-Type: application/json');
        echo json_encode($relations);
    }

    // Créer une nouvelle relation pack-événement
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id_pack']) || !isset($data['id_event'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pack et ID événement requis']);
            return;
        }

        $statut = $data['statut'] ?? 'actif';
        $packEvent = new PackEvent(null, $data['id_pack'], $data['id_event'], $statut);
        
        if ($packEvent->create()) {
            http_response_code(201);
            echo json_encode(['success' => 'Relation créée avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création']);
        }
    }

    // Mettre à jour une relation
    public function update(): void {
        $id_pack_event = $_GET['id'] ?? null;
        if (!$id_pack_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID relation requis']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $packEvent = PackEvent::getById($id_pack_event);
        
        if (!$packEvent) {
            http_response_code(404);
            echo json_encode(['error' => 'Relation non trouvée']);
            return;
        }

        // Mettre à jour les propriétés
        if (isset($data['id_pack'])) $packEvent->setIdPack($data['id_pack']);
        if (isset($data['id_event'])) $packEvent->setIdEvent($data['id_event']);
        if (isset($data['statut'])) $packEvent->setStatut($data['statut']);
        
        if ($packEvent->update()) {
            echo json_encode(['success' => 'Relation mise à jour avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
    }

    // Supprimer une relation
    public function delete(): void {
        $id_pack_event = $_GET['id'] ?? null;
        if (!$id_pack_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID relation requis']);
            return;
        }

        $packEvent = PackEvent::getById($id_pack_event);
        
        if (!$packEvent) {
            http_response_code(404);
            echo json_encode(['error' => 'Relation non trouvée']);
            return;
        }

        if ($packEvent->delete()) {
            echo json_encode(['success' => 'Relation supprimée avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression']);
        }
    }

    // Lier un pack à un événement
    public function link(): void {
        $id_pack = $_POST['id_pack'] ?? null;
        $id_event = $_POST['id_event'] ?? null;
        $statut = $_POST['statut'] ?? 'actif';

        if (!$id_pack || !$id_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pack et ID événement requis']);
            return;
        }

        if (PackEvent::linkPackEvent($id_pack, $id_event, $statut)) {
            echo json_encode(['success' => 'Pack lié à l\'événement avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la liaison']);
        }
    }

    // Dissocier un pack d'un événement
    public function unlink(): void {
        $id_pack = $_POST['id_pack'] ?? null;
        $id_event = $_POST['id_event'] ?? null;

        if (!$id_pack || !$id_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pack et ID événement requis']);
            return;
        }

        if (PackEvent::unlinkPackEvent($id_pack, $id_event)) {
            echo json_encode(['success' => 'Pack dissocié de l\'événement avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la dissociation']);
        }
    }

    // Obtenir les packs disponibles pour un événement
    public function getAvailablePacks(): void {
        $id_event = $_GET['id_event'] ?? null;
        if (!$id_event) {
            http_response_code(400);
            echo json_encode(['error' => 'ID événement requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            $sql = "SELECT p.* FROM pack p 
                    LEFT JOIN pack_event pe ON p.id_pack = pe.id_pack AND pe.id_event = ?
                    WHERE pe.id_pack IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_event]);
            $packs = $stmt->fetchAll();
            
            header('Content-Type: application/json');
            echo json_encode($packs);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
    }

    // Obtenir les événements disponibles pour un pack
    public function getAvailableEvents(): void {
        $id_pack = $_GET['id_pack'] ?? null;
        if (!$id_pack) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pack requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            $sql = "SELECT e.* FROM evente e 
                    LEFT JOIN pack_event pe ON e.id_event = pe.id_event AND pe.id_pack = ?
                    WHERE pe.id_event IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_pack]);
            $events = $stmt->fetchAll();
            
            header('Content-Type: application/json');
            echo json_encode($events);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
    }

    // Router les requêtes
    public function handleRequest(): void {
        $action = $_GET['action'] ?? '';

        $adminActions = ['getAll', 'create', 'update', 'delete', 'link', 'unlink', 'getAvailablePacks', 'getAvailableEvents'];
        if (in_array($action, $adminActions, true)) {
            UserController::requireAdmin();
        }
        
        switch ($action) {
            case 'getAll':
                $this->getAll();
                break;
            case 'getByPack':
                $this->getByPack();
                break;
            case 'getByEvent':
                $this->getByEvent();
                break;
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'link':
                $this->link();
                break;
            case 'unlink':
                $this->unlink();
                break;
            case 'getAvailablePacks':
                $this->getAvailablePacks();
                break;
            case 'getAvailableEvents':
                $this->getAvailableEvents();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action non trouvée']);
        }
    }
}

// Gérer la requête
if (basename($_SERVER['PHP_SELF']) === 'PackEventController.php') {
    $controller = new PackEventController();
    $controller->handleRequest();
}
?>
