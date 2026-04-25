<?php
require_once __DIR__ . '/../model/Publication.php';   // ✅ nom de fichier corrigé

class PublicationController {

    private $model;

    public function __construct($db = null) {
        $this->model = new PublicationModel();
    }

    // ── FRONT : liste paginée ─────────────────────────────────────────────────
    public function index() {
        $search    = $_GET['search']    ?? '';
        $categorie = $_GET['categorie'] ?? '';
        $tri       = $_GET['tri']       ?? 'date';
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 8;

        $publications = $this->model->getAllPublications($search, $categorie, $tri, $page, $perPage);
        $total        = $this->model->countPublications($search, $categorie);
        $totalPages   = ceil($total / $perPage);

        $liked_ids = [];
        if (!empty($_SESSION['id_user'])) {
            foreach ($publications as $p) {
                if ($this->model->hasLiked($p['id_publication'], $_SESSION['id_user'])) {
                    $liked_ids[] = $p['id_publication'];
                }
            }
        }

        require __DIR__ . '/../view/frontoffice/listPublication.php';
    }

    // ── FRONT : détail + commentaires ─────────────────────────────────────────
    public function show($id) {
        $id = (int)$id;
        if (!$id) die("ID manquant");

        $this->model->incrementerVues($id);
        $publication = $this->model->getPublicationById($id);

        require_once __DIR__ . '/../controller/CommentaireController.php';
        $commentController = new CommentaireController();
        $commentaires      = $commentController->listCommentaires($id);

        $id_user_session = $_SESSION['id_user'] ?? null;
        $hasLiked = $id_user_session ? $this->model->hasLiked($id, $id_user_session) : false;

        require __DIR__ . '/../view/frontoffice/detailPublication.php';
    }

    // ── FRONT : ajout ─────────────────────────────────────────────────────────
    public function add() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titre     = trim($_POST['titre']   ?? '');
            $contenu   = trim($_POST['contenu'] ?? '');
            $categorie = $_POST['categorie']    ?? 'general';
            $id_user   = $_SESSION['id_user']   ?? 1;

            if (empty($titre) || empty($contenu)) {
                $_SESSION['error'] = "Titre et contenu sont obligatoires";
                header("Location: index.php?action=addPublication");
                exit;
            }

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = $this->model->uploadImage($_FILES['image']);
                if (isset($upload['error'])) die($upload['error']);
                $image = $upload['filename'];
            }

            $is_event   = !empty($_POST['is_event']) ? 1 : 0;
            $event_date = $_POST['event_date'] ?? null;
            $event_lieu = $_POST['event_lieu'] ?? null;

            $this->model->addPublication($titre, $contenu, $id_user, $categorie, $image, $is_event, $event_date, $event_lieu);

            $_SESSION['flash_success'] = "Publication ajoutée avec succès !";
            header("Location: index.php?action=front");
            exit;
        }

        require __DIR__ . '/../view/frontoffice/addPublication.php';
    }

    // ── FRONT : modification ──────────────────────────────────────────────────
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) die("ID manquant");

        $publication = $this->model->getPublicationById($id);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titre     = trim($_POST['titre']   ?? '');
            $contenu   = trim($_POST['contenu'] ?? '');
            $categorie = $_POST['categorie']    ?? 'general';

            if (empty($titre) || empty($contenu)) {
                $_SESSION['error'] = "Titre et contenu obligatoires";
                header("Location: index.php?action=editPublication&id=$id");
                exit;
            }

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = $this->model->uploadImage($_FILES['image']);
                if (isset($upload['error'])) die($upload['error']);
                $image = $upload['filename'];
            }

            $is_event   = !empty($_POST['is_event']) ? 1 : 0;
            $event_date = $_POST['event_date'] ?? null;
            $event_lieu = $_POST['event_lieu'] ?? null;

            $this->model->updatePublication($id, $titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu);

            $_SESSION['flash_success'] = "Publication modifiée !";
            header("Location: index.php?action=front");
            exit;
        }

        require __DIR__ . '/../view/frontoffice/editPublication.php';
    }

    // ── FRONT : suppression ───────────────────────────────────────────────────
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) die("ID manquant pour suppression");
        $this->model->deletePublication($id);
        header("Location: index.php?action=front");
        exit;
    }

    // ── BACK : dashboard stats ────────────────────────────────────────────────
    public function dashboard() {
        $stats = $this->model->getStats();
        require __DIR__ . '/../view/backoffice/dashboard.php';
    }

    // ── BACK : liste publications ─────────────────────────────────────────────
    public function listBackoffice() {
        $publications = $this->model->getAllPublications('', '', 'date', 1, 1000);
        require __DIR__ . '/../view/backoffice/listPublications.php';
    }
}