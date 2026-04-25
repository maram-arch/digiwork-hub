<?php
session_start();

require_once "config.php";
require_once "controller/PublicationController.php";
require_once "controller/CommentaireController.php";
require_once "model/CommentaireModel.php";

$db = getConnection();

$pubController = new PublicationController($db);
$comController = new CommentaireController();

$action = $_GET['action'] ?? 'front';

// ─── AJAX : like publication ──────────────────────────────────────────────────
if ($action === 'like') {
    header('Content-Type: application/json');
    $id_pub  = (int)($_POST['id'] ?? 0);
    $id_user = $_SESSION['id_user'] ?? null;
    if (!$id_user) { echo json_encode(['error' => 'Connectez-vous pour liker']); exit; }
    require_once "model/Publication.php";
    $model  = new PublicationModel();
    $result = $model->toggleLike($id_pub, $id_user);
    $pub    = $model->getPublicationById($id_pub);
    $result['nb_likes'] = (int)$pub['nb_likes'];
    echo json_encode($result);
    exit;
}

// ─── AJAX : like commentaire ──────────────────────────────────────────────────
if ($action === 'likeComment') {
    header('Content-Type: application/json');
    $id_com  = (int)($_POST['id'] ?? 0);
    $id_user = $_SESSION['id_user'] ?? null;
    if (!$id_user) { echo json_encode(['error' => 'Connectez-vous pour liker']); exit; }
    $result = $comController->toggleLike($id_com, $id_user);
    echo json_encode($result);
    exit;
}

// ─── Ajout commentaire ────────────────────────────────────────────────────────
if ($action === 'addComment') {
    $contenu = trim($_POST['contenu'] ?? '');
    $id_pub  = (int)($_POST['id_publication'] ?? 0);
    if (!empty($contenu) && $id_pub) {
        $commentaire = new Commentaire($contenu, $id_pub);
        $comController->addCommentaire($commentaire);
    }
    header("Location: index.php?action=detail&id=" . $id_pub);
    exit;
}

// ─── Switch principal ─────────────────────────────────────────────────────────
switch ($action) {

    case 'front':
    case 'list':
        $pubController->index();
        break;

    case 'addPublication':
        $pubController->add();
        break;

    case 'deletePublication':
        $pubController->delete();
        break;

    case 'editPublication':
        $pubController->edit();
        break;

    case 'detail':
    case 'voir':
        $pubController->show($_GET['id'] ?? 0);
        break;

    case 'deleteComment':
        $id_com = (int)($_GET['id'] ?? 0);
        $id_pub = (int)($_GET['pub'] ?? 0);
        $comController->deleteCommentaire($id_com);
        header("Location: index.php?action=detail&id=" . $id_pub);
        exit;

    case 'editComment':
        $comController->editAction();
        break;

    case 'dashboard':
        $pubController->dashboard();
        break;

    case 'listPublications':
    case 'admin_publications':
        $pubController->listBackoffice();
        break;

    case 'listCommentaires':
    case 'admin_commentaires':
        $comController->listBackoffice();
        break;

    default:
        $pubController->index();
        break;
}