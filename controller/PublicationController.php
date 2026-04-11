<?php
class PublicationController {
    private $model;

    public function __construct() {
        require_once __DIR__ . '/../model/publication.php';
        $this->model = new PublicationModel();
    }

    public function listPublications() {
        $search    = trim($_GET['search'] ?? '');
        $categorie = $_GET['categorie'] ?? '';
        $tri       = $_GET['tri'] ?? 'date';
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 8;

        $categories_valides = ['general','stage','job','question','evenement'];
        if (!in_array($categorie, $categories_valides)) $categorie = '';

        $tris_valides = ['date','date_asc','likes','vues'];
        if (!in_array($tri, $tris_valides)) $tri = 'date';

        $publications = $this->model->getAllPublications($search, $categorie, $tri, $page, $perPage);
        $total        = $this->model->countPublications($search, $categorie);
        $totalPages   = max(1, ceil($total / $perPage));

        $id_user   = $_SESSION['id_user'] ?? null;
        $liked_ids = [];
        if ($id_user) {
            foreach ($publications as $pub) {
                if ($this->model->hasLiked($pub['id_publication'], $id_user)) {
                    $liked_ids[] = $pub['id_publication'];
                }
            }
        }

        require_once __DIR__ . '/../view/frontoffice/listPublication.php';
    }

    public function addPublication() {
        $error      = '';
        $titre      = '';
        $contenu    = '';
        $categorie  = 'general';
        $is_event   = 0;
        $event_date = '';
        $event_lieu = '';

        $id_user = $_SESSION['id_user'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre      = trim($_POST['titre']      ?? '');
            $contenu    = trim($_POST['contenu']    ?? '');
            $categorie  = $_POST['categorie']       ?? 'general';
            $is_event   = isset($_POST['is_event']) ? 1 : 0;
            $event_date = trim($_POST['event_date'] ?? '');
            $event_lieu = trim($_POST['event_lieu'] ?? '');

            $categories_valides = ['general','stage','job','question','evenement'];
            if (!in_array($categorie, $categories_valides)) $categorie = 'general';

            $errors = [];
            if ($titre === '')   $errors[] = 'Le titre est obligatoire.';
            if (strlen($titre) > 100) $errors[] = 'Titre trop long (max 100 caractères).';
            if ($contenu === '') $errors[] = 'Le contenu est obligatoire.';
            if ($is_event && empty($event_date)) $errors[] = "La date de l'événement est obligatoire.";
            if (!$id_user)       $errors[] = 'Vous devez être connecté.';

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = $this->model->uploadImage($_FILES['image']);
                if (isset($upload['error'])) {
                    $errors[] = $upload['error'];
                } else {
                    $image = $upload['filename'];
                }
            }

            if (empty($errors)) {
                if ($this->model->addPublication($titre, $contenu, $id_user, $categorie, $image, $is_event, $event_date ?: null, $event_lieu ?: null)) {
                    $_SESSION['flash_success'] = '✅ Publication ajoutée avec succès !';
                    header("Location: index.php?action=list");
                    exit();
                } else {
                    $error = "Erreur lors de l'ajout.";
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        require_once __DIR__ . '/../view/frontoffice/addPublication.php';
    }

    public function editPublication() {
        $id      = $_GET['id'] ?? null;
        $error   = '';
        $id_user = $_SESSION['id_user'] ?? null;

        if (!$id || !is_numeric($id)) {
            header("Location: index.php?action=list"); exit();
        }

        $publication = $this->model->getPublicationById($id);
        if (!$publication) {
            header("Location: index.php?action=list"); exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre      = trim($_POST['titre']      ?? '');
            $contenu    = trim($_POST['contenu']    ?? '');
            $categorie  = $_POST['categorie']       ?? 'general';
            $is_event   = isset($_POST['is_event']) ? 1 : 0;
            $event_date = trim($_POST['event_date'] ?? '');
            $event_lieu = trim($_POST['event_lieu'] ?? '');

            $categories_valides = ['general','stage','job','question','evenement'];
            if (!in_array($categorie, $categories_valides)) $categorie = 'general';

            $errors = [];
            if ($titre === '')   $errors[] = 'Le titre est obligatoire.';
            if (strlen($titre) > 100) $errors[] = 'Titre trop long (max 100 caractères).';
            if ($contenu === '') $errors[] = 'Le contenu est obligatoire.';

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = $this->model->uploadImage($_FILES['image']);
                if (isset($upload['error'])) {
                    $errors[] = $upload['error'];
                } else {
                    if (!empty($publication['image'])) {
                        $old = __DIR__ . '/../view/frontoffice/assets/img/publications/' . $publication['image'];
                        if (file_exists($old)) unlink($old);
                    }
                    $image = $upload['filename'];
                }
            }

            if (empty($errors)) {
                if ($this->model->updatePublication($id, $titre, $contenu, $categorie, $image, $is_event, $event_date ?: null, $event_lieu ?: null)) {
                    $_SESSION['flash_success'] = '✅ Publication modifiée avec succès !';
                    header("Location: index.php?action=list"); exit();
                } else {
                    $error = "Erreur lors de la modification.";
                }
            } else {
                $error = implode('<br>', $errors);
            }

            $publication['titre']      = $titre;
            $publication['contenu']    = $contenu;
            $publication['categorie']  = $categorie;
            $publication['is_event']   = $is_event;
            $publication['event_date'] = $event_date;
            $publication['event_lieu'] = $event_lieu;
        }

        require_once __DIR__ . '/../view/frontoffice/editPublication.php';
    }

    public function deletePublication() {
        $id      = $_GET['id'] ?? null;
        $id_user = $_SESSION['id_user'] ?? null;

        if ($id && is_numeric($id)) {
            if ($id_user && !$this->model->isOwner($id, $id_user)) {
                $_SESSION['flash_error'] = '⛔ Vous ne pouvez supprimer que vos propres publications.';
                header("Location: index.php?action=list"); exit();
            }
            $this->model->deletePublication($id);
            $_SESSION['flash_success'] = '🗑️ Publication supprimée.';
        }
        header("Location: index.php?action=list"); exit();
    }

    public function likePublication() {
        header('Content-Type: application/json');
        $id_user = $_SESSION['id_user'] ?? null;

        if (!$id_user) {
            echo json_encode(['error' => 'Non connecté']); exit();
        }

        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            echo json_encode(['error' => 'ID invalide']); exit();
        }

        $result = $this->model->toggleLike($id, $id_user);
        $pub = $this->model->getPublicationById($id);
        $result['nb_likes'] = $pub['nb_likes'];
        echo json_encode($result);
        exit();
    }

    public function voirPublication() {
        $id = $_GET['id'] ?? null;
        if ($id && is_numeric($id)) {
            $this->model->incrementerVues($id);
            $publication = $this->model->getPublicationById($id);
            if ($publication) {
                require_once __DIR__ . '/../view/frontoffice/showPublication.php';
                exit();
            }
        }
        header("Location: index.php?action=list"); exit();
    }
}