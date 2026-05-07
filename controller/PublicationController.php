<?php
class PublicationController {
    private $model;

    public function __construct() {
        require_once __DIR__ . '/../model/publication.php';   // ← Chemin corrigé selon ta structure
        $this->model = new PublicationModel();
    }

    public function listPublications() {
        $publications = $this->model->getAllPublications();
        $total        = $this->model->countPublications();
        require_once __DIR__ . '/../view/frontoffice/listPublication.php';   // nom que tu utilises
    }

    public function addPublication() {
        $error   = '';
        $titre   = '';
        $contenu = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre   = trim($_POST['titre'] ?? '');
            $contenu = trim($_POST['contenu'] ?? '');
            $id_user = $_SESSION['id_user'] ?? 1;   // temporaire

            if ($titre !== '' && $contenu !== '') {
                if ($this->model->addPublication($titre, $contenu, $id_user)) {
                    header("Location: index.php?action=list");
                    exit();
                } else {
                    $error = "Erreur lors de l'ajout.";
                }
            } else {
                $error = "Titre et contenu obligatoires.";
            }
        }

        require_once __DIR__ . '/../view/frontoffice/addPublication.php';
    }

    public function editPublication() {
        $id = $_GET['id'] ?? null;
        $error = '';

        if (!$id || !is_numeric($id)) {
            header("Location: index.php?action=list");
            exit();
        }

        $publication = $this->model->getPublicationById($id);
        if (!$publication) {
            header("Location: index.php?action=list");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre   = trim($_POST['titre'] ?? '');
            $contenu = trim($_POST['contenu'] ?? '');

            if ($titre !== '' && $contenu !== '') {
                if ($this->model->updatePublication($id, $titre, $contenu)) {
                    header("Location: index.php?action=list");
                    exit();
                } else {
                    $error = "Erreur lors de la modification.";
                }
            } else {
                $error = "Titre et contenu obligatoires.";
            }
            $publication['titre']   = $titre;
            $publication['contenu'] = $contenu;
        }

        require_once __DIR__ . '/../view/frontoffice/editPublication.php';
    }

    public function deletePublication() {
        $id = $_GET['id'] ?? null;
        if ($id && is_numeric($id)) {
            $this->model->deletePublication($id);
        }
        header("Location: index.php?action=list");
        exit();
    }
}