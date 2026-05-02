<?php
<<<<<<< HEAD
require_once __DIR__ . '/../model/Publication.php';
require_once __DIR__ . '/../config/config.php';

class PublicationController
{
    // Ajouter une publication (reçoit un objet Publication)
    public function addPublication($publication)
    {
        return $publication->addPublication();
    }

    // Récupérer une publication avec auteur
    public function getPublication($id)
    {
        return Publication::getByIdWithUser($id);
    }

    // Mettre à jour une publication (paramètres explicites pour éviter les getters)
    public function updatePublication($id, $titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu)
    {
        $db = Config::getConnexion();
        $sql = "UPDATE forums SET titre=?, contenu=?, categorie=?, image=?, is_event=?, event_date=?, event_lieu=? WHERE id_publication=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu, $id]);
    }

    // Supprimer une publication
    public function deletePublication($id)
    {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM forums WHERE id_publication = ?");
        return $stmt->execute([$id]);
    }

    // Liste simple pour l'accueil (utilisée dans index.php front)
    public function listPublication()
    {
        $sql = "SELECT f.*, u.nom, u.prenom
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.statut = 'active'
                ORDER BY f.date_publication DESC";
        return Config::getConnexion()->query($sql);
    }
}
?>
=======
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
>>>>>>> 6b3b218dc29227adaacddd025b9d802292528038
