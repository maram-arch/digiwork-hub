<?php
session_start();
require_once('../config/config.php');
require_once('../model/Abonnement.php');

$abo = new Abonnement();

function isAdmin(): bool {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function wantsJson(): bool {
    if (isset($_POST['ajax'])) return true;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return is_string($accept) && strpos($accept, 'application/json') !== false;
}

function denyIfNotAdmin(): void {
    if (isAdmin()) return;
    if (wantsJson()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Accès refusé (admin requis).']);
        exit;
    }
    $_SESSION['flash'] = 'Accès refusé (admin requis).';
    header('Location: /view/front/login.php');
    exit;
}

// Return JSON for API consumers
// Return JSON for API consumers: all abonnements (admin) or per-user via getMine
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getAll') {
        denyIfNotAdmin();
        header('Content-Type: application/json');
        $abonnements = $abo->getAllAbonnements();
        echo json_encode($abonnements);
        exit;
    }

    if ($_GET['action'] === 'getMine') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }
        $userId = intval($_SESSION['user_id']);
        $abonnements = $abo->getByUser($userId);
        echo json_encode($abonnements);
        exit;
    }
}

// Handle POST requests (both AJAX and normal form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Admin Deletion (AJAX or form)
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        denyIfNotAdmin();
        $abo->delete($_POST['id']);

        // If AJAX, return JSON
        if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement supprimé avec succès']);
            exit;
        }

        // Otherwise redirect back to admin page with a flash
        $_SESSION['flash'] = 'Abonnement supprimé avec succès';
        header('Location: /view/back/dashboard_abonnements.php');
        exit;
    }

    // Subscribe action (AJAX POST or normal form POST)
    if ($_POST['action'] === 'subscribe') {
        $packId = intval($_POST['pack_id']);
        $nom = $_POST['nom'] ?? null;
        $tel = $_POST['tel'] ?? null;
        $dateDeb = $_POST['date_deb'] ?? null;
        $dateFin = $_POST['date_fin'] ?? null;
        $status = $_POST['status'] ?? 'actif';
        
        // If manual form data is provided, create user with that data
        if ($nom && $tel) {
            global $pdo;
            try {
                // Validate tel is numeric
                if (!is_numeric($tel)) {
                    throw new \Exception('Le téléphone doit être un nombre');
                }
                
                $telInt = intval($tel);
                if ($telInt <= 0) {
                    throw new \Exception('Le téléphone doit être un nombre positif');
                }
                
                // Get next user ID explicitly
                $maxIdStmt = $pdo->query("SELECT MAX(id_user) as max_id FROM `user`");
                $maxIdResult = $maxIdStmt->fetch(PDO::FETCH_ASSOC);
                $nextUserId = ($maxIdResult['max_id'] ?? 0) + 1;
                
                // Create user with provided data and explicit ID
                $sql = "INSERT INTO `user` (id_user, email, mdp, tel) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $email = $nom . '@digiwork.com';
                $password = 'default_password';
                
                error_log("Creating user: id=$nextUserId, email=$email, tel=$telInt");
                $result = $stmt->execute([$nextUserId, $email, $password, $telInt]);
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("User insert error: " . print_r($errorInfo, true));
                    throw new \Exception('Failed to create user: ' . $errorInfo[2]);
                }
                
                $userId = $nextUserId;
                error_log("User created with ID: $userId");
                
                // Create abonnement with provided data
                // Get next abonnement ID explicitly
                $maxAbIdStmt = $pdo->query("SELECT MAX(`id-abonnement`) as max_id FROM `abonnement`");
                $maxAbIdResult = $maxAbIdStmt->fetch(PDO::FETCH_ASSOC);
                $nextAbId = ($maxAbIdResult['max_id'] ?? 0) + 1;
                
                $sql2 = "INSERT INTO `abonnement` (`id-abonnement`, `id-user`, `date-deb`, `date-fin`, `status`) VALUES (?, ?, ?, ?, ?)";
                $stmt2 = $pdo->prepare($sql2);
                
                error_log("Creating abonnement: id=$nextAbId, userId=$userId, dateDeb=$dateDeb, dateFin=$dateFin, status=$status");
                $result2 = $stmt2->execute([$nextAbId, $userId, $dateDeb, $dateFin, $status]);
                
                if (!$result2) {
                    $errorInfo = $stmt2->errorInfo();
                    error_log("Abonnement insert error: " . print_r($errorInfo, true));
                    throw new \Exception('Failed to create abonnement: ' . $errorInfo[2]);
                }
                
                $abId = $nextAbId;
                error_log("Abonnement created with ID: $abId");
                
                // Link abonnement to pack
                $sql3 = "INSERT INTO `abon-pack` (`id-pack`, `id-abonnement`) VALUES (?, ?)";
                $stmt3 = $pdo->prepare($sql3);
                $stmt3->execute([$packId, $abId]);
                
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès', 'abonnement_id' => $abId]);
                    exit;
                }
                
                $_SESSION['flash'] = 'Abonnement créé avec succès';
                header('Location: /view/front/abonnement.php');
                exit;
            } catch (\Exception $e) {
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage()]);
                    exit;
                }
                $_SESSION['flash'] = 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage();
                header('Location: /view/front/packs.php');
                exit;
            }
        }
        
        // Fallback to original behavior if no manual data
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner.']);
                exit;
            }
            $_SESSION['flash'] = 'Vous devez être connecté pour vous abonner.';
            header('Location: /view/front/login.php');
            exit;
        }

        $abId = $abo->subscribe($userId, $packId);
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Abonnement créé avec succès', 'abonnement_id' => $abId]);
            exit;
        }

        $_SESSION['flash'] = 'Abonnement créé avec succès';
        header('Location: /view/front/abonnement.php');
        exit;
    }

    // Update action (AJAX POST only)
    if ($_POST['action'] === 'update' && isset($_POST['id'])) {
        denyIfNotAdmin();
        
        $id = intval($_POST['id']);
        $status = $_POST['status'] ?? '';
        $dateFin = $_POST['date_fin'] ?? '';
        
        $result = $abo->update($id, $status, $dateFin);
        
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Abonnement mis à jour avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }
}

// Default: redirect
header('Location: /view/front/packs.php');
exit;
?>
