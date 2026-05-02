<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/WhatsAppNotifier.php';

class Publication
{
    private $pdo;
    private $id_publication;
    private $titre;
    private $contenu;
    private $date_publication;
    private $id_user;
    private $categorie;
    private $image;
    private $statut;
    private $nb_vues;
    private $nb_likes;
    private $is_event;
    private $event_date;
    private $event_lieu;

    public function __construct($id = null, $titre = null, $contenu = null, $date_pub = null, $id_user = null, $categorie = 'general', $image = null, $statut = 'active', $nb_vues = 0, $nb_likes = 0, $is_event = 0, $event_date = null, $event_lieu = null)
    {
        $this->pdo = Config::getConnexion();
        $this->id_publication = $id;
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->date_publication = $date_pub ?: date('Y-m-d H:i:s');
        $this->id_user = $id_user;
        $this->categorie = $categorie;
        $this->image = $image;
        $this->statut = $statut;
        $this->nb_vues = $nb_vues;
        $this->nb_likes = $nb_likes;
        $this->is_event = $is_event;
        $this->event_date = $event_date;
        $this->event_lieu = $event_lieu;
    }

    // Getters
    public function getIdPublication() { return $this->id_publication; }
    public function getTitre() { return $this->titre; }
    public function getContenu() { return $this->contenu; }
    public function getDatePublication() { return $this->date_publication; }
    public function getIdUser() { return $this->id_user; }
    public function getCategorie() { return $this->categorie; }
    public function getImage() { return $this->image; }
    public function getStatut() { return $this->statut; }
    public function getNbVues() { return $this->nb_vues; }
    public function getNbLikes() { return $this->nb_likes; }
    public function getIsEvent() { return $this->is_event; }
    public function getEventDate() { return $this->event_date; }
    public function getEventLieu() { return $this->event_lieu; }

    // Setters
    public function setTitre($t) { $this->titre = $t; }
    public function setContenu($c) { $this->contenu = $c; }
    public function setCategorie($c) { $this->categorie = $c; }
    public function setImage($i) { $this->image = $i; }
    public function setIsEvent($e) { $this->is_event = $e; }
    public function setEventDate($d) { $this->event_date = $d; }
    public function setEventLieu($l) { $this->event_lieu = $l; }

    // Ajout en base
    public function addPublication(): bool
    {
        $sql = "INSERT INTO forums (titre, contenu, date_publication, id_user, categorie, image, statut, nb_vues, nb_likes, is_event, event_date, event_lieu)
                VALUES (:titre, :contenu, :date_publication, :id_user, :categorie, :image, :statut, :nb_vues, :nb_likes, :is_event, :event_date, :event_lieu)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titre' => $this->titre,
            ':contenu' => $this->contenu,
            ':date_publication' => $this->date_publication,
            ':id_user' => $this->id_user,
            ':categorie' => $this->categorie,
            ':image' => $this->image,
            ':statut' => $this->statut,
            ':nb_vues' => $this->nb_vues,
            ':nb_likes' => $this->nb_likes,
            ':is_event' => $this->is_event,
            ':event_date' => $this->event_date,
            ':event_lieu' => $this->event_lieu
        ]);
    }

    // Mise à jour
    public function updatePublication($id_publication): bool
    {
        $sql = "UPDATE forums SET titre=:titre, contenu=:contenu, categorie=:categorie, image=:image, is_event=:is_event, event_date=:event_date, event_lieu=:event_lieu
                WHERE id_publication=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id_publication,
            ':titre' => $this->titre,
            ':contenu' => $this->contenu,
            ':categorie' => $this->categorie,
            ':image' => $this->image,
            ':is_event' => $this->is_event,
            ':event_date' => $this->event_date,
            ':event_lieu' => $this->event_lieu
        ]);
    }

    // ========== METHODES STATIQUES ==========

    public static function getAllWithFilters($categorie = 'all', $tri = 'date', $page = 1, $perPage = 8)
    {
        $pdo = Config::getConnexion();
        $offset = ($page - 1) * $perPage;
        $where = "WHERE f.statut = 'active'";
        $params = [];
        if ($categorie !== 'all') {
            $where .= " AND f.categorie = :categorie";
            $params['categorie'] = $categorie;
        }
        switch ($tri) {
            case 'likes': $order = "ORDER BY f.nb_likes DESC"; break;
            case 'vues':  $order = "ORDER BY f.nb_vues DESC"; break;
            default:      $order = "ORDER BY f.date_publication DESC";
        }
        $sql = "SELECT f.*, u.nom, u.prenom
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                $where
                $order
                LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countWithFilters($categorie = 'all')
    {
        $pdo = Config::getConnexion();
        $where = "WHERE statut = 'active'";
        $params = [];
        if ($categorie !== 'all') {
            $where .= " AND categorie = :categorie";
            $params['categorie'] = $categorie;
        }
        $sql = "SELECT COUNT(*) FROM forums $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getByIdWithUser($id)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT f.*, u.nom, u.prenom FROM forums f LEFT JOIN user u ON f.id_user = u.id_user WHERE f.id_publication = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function incrementVues($id)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE forums SET nb_vues = nb_vues + 1 WHERE id_publication = ?");
        $stmt->execute([$id]);
    }

    // Likes publications
    public static function hasLiked($id_publication, $id_user)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM publication_likes WHERE id_publication = ? AND id_user = ?");
        $stmt->execute([$id_publication, $id_user]);
        return (bool)$stmt->fetchColumn();
    }

    public static function toggleLike($id_publication, $id_user)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM publication_likes WHERE id_publication = ? AND id_user = ?");
        $stmt->execute([$id_publication, $id_user]);

        if ($stmt->fetchColumn()) {
            // Unlike
            $pdo->prepare("DELETE FROM publication_likes WHERE id_publication = ? AND id_user = ?")->execute([$id_publication, $id_user]);
            $pdo->prepare("UPDATE forums SET nb_likes = nb_likes - 1 WHERE id_publication = ?")->execute([$id_publication]);
            return ['action' => 'unliked'];
        } else {
            // Like
            $pdo->prepare("INSERT INTO publication_likes (id_publication, id_user) VALUES (?, ?)")->execute([$id_publication, $id_user]);
            $pdo->prepare("UPDATE forums SET nb_likes = nb_likes + 1 WHERE id_publication = ?")->execute([$id_publication]);

            // Envoi de notification WhatsApp au propriétaire (sauf si c'est lui-même)
            $notifier = new WhatsAppNotifier();
            $notifier->notifyOwner($id_publication, $id_user, 'like');

            return ['action' => 'liked'];
        }
    }

    // Favoris
    public static function addFavori($id_publication, $id_user) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("INSERT IGNORE INTO favoris (id_publication, id_user) VALUES (?, ?)");
        return $stmt->execute([$id_publication, $id_user]);
    }
    public static function removeFavori($id_publication, $id_user) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_publication = ? AND id_user = ?");
        return $stmt->execute([$id_publication, $id_user]);
    }
    public static function isFavori($id_publication, $id_user) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM favoris WHERE id_publication = ? AND id_user = ?");
        $stmt->execute([$id_publication, $id_user]);
        return (bool)$stmt->fetchColumn();
    }

    // Statistiques
    public static function getTopPublications($limit = 5) {
        $pdo = Config::getConnexion();
        $sql = "SELECT f.*, u.nom, u.prenom,
                (f.nb_likes * 2 + (SELECT COUNT(*) FROM commentaire WHERE id_publication = f.id_publication) * 1.5 - DATEDIFF(NOW(), f.date_publication) * 0.1) as score
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.statut = 'active'
                ORDER BY score DESC
                LIMIT $limit";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTrending($limit = 5) {
        $pdo = Config::getConnexion();
        $sql = "SELECT f.*, u.nom, u.prenom,
                (f.nb_likes + (SELECT COUNT(*) FROM commentaire WHERE id_publication = f.id_publication AND date_commentaire > DATE_SUB(NOW(), INTERVAL 1 DAY))) as trending_score
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.statut = 'active' AND DATEDIFF(NOW(), f.date_publication) <= 7
                ORDER BY trending_score DESC
                LIMIT $limit";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopUsers($limit = 5) {
        $pdo = Config::getConnexion();
        $sql = "SELECT u.id_user, u.nom, u.prenom,
                COUNT(DISTINCT f.id_publication) as nb_publications,
                COUNT(DISTINCT c.id_commentaire) as nb_commentaires,
                (COUNT(DISTINCT f.id_publication) * 2 + COUNT(DISTINCT c.id_commentaire)) as score
                FROM user u
                LEFT JOIN forums f ON u.id_user = f.id_user AND f.statut = 'active'
                LEFT JOIN commentaire c ON u.id_user = c.id_user
                GROUP BY u.id_user
                ORDER BY score DESC
                LIMIT $limit";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEngagementByCategory() {
        $pdo = Config::getConnexion();
        $sql = "SELECT categorie,
                COUNT(*) as nb_pubs,
                SUM(nb_likes) as total_likes,
                (SELECT COUNT(*) FROM commentaire WHERE commentaire.id_publication = forums.id_publication) as total_coms
                FROM forums
                GROUP BY categorie";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getActivityTimeline() {
        $pdo = Config::getConnexion();
        $pubs = $pdo->query("SELECT DATE(date_publication) as jour, COUNT(*) as nb FROM forums WHERE date_publication > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_publication)")->fetchAll();
        $coms = $pdo->query("SELECT DATE(date_commentaire) as jour, COUNT(*) as nb FROM commentaire WHERE date_commentaire > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_commentaire)")->fetchAll();
        return ['publications' => $pubs, 'commentaires' => $coms];
    }
}
?>