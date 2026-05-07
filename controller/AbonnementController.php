<?php
require_once __DIR__ . '/../model/Abonnement.php';
require_once __DIR__ . '/../model/Pack.php';
require_once __DIR__ . '/../config/config.php';

class AbonnementController {
    private Abonnement $abonnementModel;
    private Pack $packModel;

    public function __construct() {
        $this->abonnementModel = new Abonnement();
        $this->packModel = new Pack();
    }

    // Récupérer tous les abonnements avec informations client et pack
    public function getAll(): void {
        try {
            $pdo = config::getConnexion();
            
            $sql = "SELECT 
                        a.`id-abonnement`,
                        a.`id-user`,
                        a.`date-deb`,
                        a.`date-fin`,
                        a.status,
                        u.email,
                        u.tel,
                        p.`nom-pack`,
                        p.prix
                    FROM abonnement a
                    LEFT JOIN `user` u ON a.`id-user` = u.id_user
                    LEFT JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                    LEFT JOIN pack p ON ap.`id-pack` = p.`id-pack`
                    ORDER BY a.`date-deb` DESC";
            
            $stmt = $pdo->query($sql);
            $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($abonnements);
        } catch (PDOException $e) {
            error_log("Erreur getAll abonnements: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }

    // Récupérer un abonnement par ID
    public function getById(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID abonnement requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            
            $sql = "SELECT 
                        a.id_abonnement,
                        a.id_user,
                        a.date_deb,
                        a.date_fin,
                        a.statut,
                        u.email,
                        u.tel,
                        p.nom_pack,
                        p.prix
                    FROM abonnement a
                    LEFT JOIN `user` u ON a.id_user = u.id_user
                    LEFT JOIN `abon-pack` ap ON a.id_abonnement = ap.`id-abonnement`
                    LEFT JOIN pack p ON ap.`id-pack` = p.id_pack
                    WHERE a.id_abonnement = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $abonnement = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($abonnement);
        } catch (PDOException $e) {
            error_log("Erreur getById abonnement: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données']);
        }
    }

    // Obtenir les abonnements de l'utilisateur connecté
    public function getMine(): void {
        session_start();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté']);
            return;
        }

        $id_user = $_SESSION['user_id'];

        try {
            $pdo = config::getConnexion();
            
            $sql = "SELECT 
                        a.`id-abonnement`,
                        a.`date-deb`,
                        a.`date-fin`,
                        a.status,
                        p.`nom-pack`,
                        p.prix,
                        p.description,
                        p.`nb-proj-max`,
                        p.`support-prioritaire`,
                        u.email,
                        u.tel
                    FROM abonnement a
                    LEFT JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                    LEFT JOIN pack p ON ap.`id-pack` = p.`id-pack`
                    LEFT JOIN `user` u ON a.`id-user` = u.id_user
                    WHERE a.`id-user` = ?
                    ORDER BY a.`date-deb` DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_user]);
            $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($abonnements);
        } catch (PDOException $e) {
            error_log("Erreur getMine abonnements: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erreur base de données']);
        }
    }

    // S'abonner à un pack (pour le frontend)
    public function subscribe(): void {
        session_start();
        
        $pack_id = $_POST['pack_id'] ?? null;
        $nom = $_POST['nom'] ?? null;
        $tel = $_POST['tel'] ?? null;
        $date_deb = $_POST['date_deb'] ?? date('Y-m-d');
        
        // Get pack information to calculate end date
        $date_fin = $_POST['date_fin'] ?? null;
        if (!$date_fin && $pack_id) {
            try {
                $pdo = config::getConnexion();
                $stmt = $pdo->prepare("SELECT `duree` FROM pack WHERE `id-pack` = ?");
                $stmt->execute([$pack_id]);
                $pack = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pack && $pack['duree']) {
                    // Calculate end date from pack duration
                    $date_obj = new DateTime($date_deb);
                    $duration = $pack['duree']; // Assuming duree is in days or format that can be parsed
                    if (is_numeric($duration)) {
                        $date_obj->add(new DateInterval('P' . $duration . 'D'));
                    } else {
                        // If duree is a date, calculate difference
                        $end_date = new DateTime($duration);
                        $date_fin = $end_date->format('Y-m-d');
                    }
                    $date_fin = $date_obj->format('Y-m-d');
                }
            } catch (Exception $e) {
                // Fallback to 30 days if pack info not found
                $date_obj = new DateTime($date_deb);
                $date_obj->add(new DateInterval('P30D'));
                $date_fin = $date_obj->format('Y-m-d');
            }
        }
        
        $status = $_POST['status'] ?? 'actif';

        if (!$pack_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID pack requis']);
            return;
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner']);
            return;
        }

        $id_user = $_SESSION['user_id'];

        try {
            $pdo = config::getConnexion();
            $pdo->beginTransaction();

            // Créer l'abonnement
            $sql = "INSERT INTO abonnement (`id-user`, `date-deb`, `date-fin`, status) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_user, $date_deb, $date_fin, $status]);
            $id_abonnement = $pdo->lastInsertId();

            // Lier l'abonnement au pack
            $sql = "INSERT INTO `abon-pack` (`id-abonnement`, `id-pack`) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_abonnement, $pack_id]);

            $pdo->commit();

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur subscribe abonnement: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage()]);
        }
    }

    // Créer un nouvel abonnement
    public function create(): void {
        $id_user = $_POST['id_user'] ?? null;
        $id_pack = $_POST['id_pack'] ?? null;
        $date_deb = $_POST['date_deb'] ?? date('Y-m-d');
        $date_fin = $_POST['date_fin'] ?? null;
        $status = $_POST['status'] ?? 'actif';

        if (!$id_user || !$id_pack) {
            http_response_code(400);
            echo json_encode(['error' => 'ID utilisateur et ID pack requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            $pdo->beginTransaction();

            // Créer l'abonnement
            $sql = "INSERT INTO abonnement (id_user, date_deb, date_fin, statut) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_user, $date_deb, $date_fin, $status]);
            $id_abonnement = $pdo->lastInsertId();

            // Lier l'abonnement au pack
            $sql = "INSERT INTO `abon-pack` (id_abonnement, id_pack) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_abonnement, $id_pack]);

            $pdo->commit();

            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur create abonnement: " . $e->getMessage());
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la création']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php?error=1');
            }
        }
    }

    // Mettre à jour un abonnement
    public function update(): void {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;
        $date_fin = $_POST['date_fin'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID abonnement requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            
            $sql = "UPDATE abonnement SET statut = ?, date_fin = ? WHERE id_abonnement = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $date_fin, $id]);

            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Abonnement mis à jour avec succès']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php');
            }
        } catch (PDOException $e) {
            error_log("Erreur update abonnement: " . $e->getMessage());
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php?error=1');
            }
        }
    }

    // Supprimer un abonnement
    public function delete(): void {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID abonnement requis']);
            return;
        }

        try {
            $pdo = config::getConnexion();
            $pdo->beginTransaction();

            // Supprimer la liaison avec le pack
            $sql = "DELETE FROM `abon-pack` WHERE `id-abonnement` = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            // Supprimer l'abonnement
            $sql = "DELETE FROM abonnement WHERE id_abonnement = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $pdo->commit();

            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Abonnement supprimé avec succès']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur delete abonnement: " . $e->getMessage());
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression']);
            } else {
                header('Location: ../view/back/dashboard_abonnements.php?error=1');
            }
        }
    }

    // Obtenir les statistiques des abonnements
    public function getStats(): void {
        try {
            $pdo = config::getConnexion();
            
            // Total des abonnements
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM abonnement");
            $total = $stmt->fetch()['total'];

            // Abonnements actifs
            $stmt = $pdo->query("SELECT COUNT(*) as actifs FROM abonnement WHERE statut = 'actif'");
            $actifs = $stmt->fetch()['actifs'];

            // Abonnements expirés
            $stmt = $pdo->query("SELECT COUNT(*) as expires FROM abonnement WHERE statut = 'expiré' OR (date_fin < CURDATE() AND statut = 'actif')");
            $expires = $stmt->fetch()['expires'];

            // Revenus mensuels
            $stmt = $pdo->query("SELECT SUM(p.prix) as revenue 
                                 FROM abonnement a 
                                 JOIN `abon-pack` ap ON a.id_abonnement = ap.`id-abonnement` 
                                 JOIN pack p ON ap.`id-pack` = p.id_pack 
                                 WHERE a.statut = 'actif'");
            $revenue = $stmt->fetch()['revenue'] ?? 0;

            header('Content-Type: application/json');
            echo json_encode([
                'total' => $total,
                'actifs' => $actifs,
                'expires' => $expires,
                'revenue' => $revenue
            ]);
        } catch (PDOException $e) {
            error_log("Erreur getStats abonnements: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données']);
        }
    }

    // Router les requêtes
    public function handleRequest(): void {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        switch ($action) {
            case 'getAll':
                $this->getAll();
                break;
            case 'getById':
                $this->getById();
                break;
            case 'getMine':
                $this->getMine();
                break;
            case 'subscribe':
                $this->subscribe();
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
            case 'getStats':
                $this->getStats();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action non trouvée']);
        }
    }
}

// Gérer la requête
if (basename($_SERVER['PHP_SELF']) === 'AbonnementController.php') {
    $controller = new AbonnementController();
    $controller->handleRequest();
}
?>