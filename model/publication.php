<?php
class PublicationModel {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../config.php';
        $this->pdo = getConnection();
    }

    public function getAllPublications($search = '', $categorie = '', $tri = 'date', $page = 1, $perPage = 8) {
        $offset  = (int)(($page - 1) * $perPage);
        $perPage = (int)$perPage;
        $params  = [];

        $sql = "SELECT f.*,
                       CONCAT(COALESCE(u.prenom,''), ' ', COALESCE(u.nom,'')) AS auteur_nom,
                       u.email AS auteur_email
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.statut = 'active'";

        if (!empty($search)) {
            $sql .= " AND (f.titre LIKE ? OR f.contenu LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($categorie)) {
            $sql .= " AND f.categorie = ?";
            $params[] = $categorie;
        }

        switch ($tri) {
            case 'likes':    $sql .= " ORDER BY f.nb_likes DESC"; break;
            case 'vues':     $sql .= " ORDER BY f.nb_vues DESC";  break;
            case 'date_asc': $sql .= " ORDER BY f.date_publication ASC"; break;
            default:         $sql .= " ORDER BY f.date_publication DESC";
        }

        $sql .= " LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPublications($search = '', $categorie = '') {
        $params = [];
        $sql = "SELECT COUNT(*) FROM forums f WHERE f.statut = 'active'";
        if (!empty($search)) {
            $sql .= " AND (f.titre LIKE ? OR f.contenu LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($categorie)) {
            $sql .= " AND f.categorie = ?";
            $params[] = $categorie;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPublicationById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT f.*,
                    CONCAT(COALESCE(u.prenom,''), ' ', COALESCE(u.nom,'')) AS auteur_nom,
                    u.email AS auteur_email
             FROM forums f
             LEFT JOIN user u ON f.id_user = u.id_user
             WHERE f.id_publication = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStats() {
        return [
            'pub'   => $this->pdo->query("SELECT COUNT(*) FROM forums")->fetchColumn(),
            'com'   => $this->pdo->query("SELECT COUNT(*) FROM commentaires")->fetchColumn(),
            'likes' => $this->pdo->query("SELECT COALESCE(SUM(nb_likes),0) FROM forums")->fetchColumn(),
            'vues'  => $this->pdo->query("SELECT COALESCE(SUM(nb_vues),0) FROM forums")->fetchColumn(),
        ];
    }

    public function addPublication($titre, $contenu, $id_user, $categorie = 'general', $image = null, $is_event = 0, $event_date = null, $event_lieu = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO forums (titre, contenu, categorie, image, statut, nb_vues, nb_likes, is_event, event_date, event_lieu, date_publication, id_user)
             VALUES (?, ?, ?, ?, 'active', 0, 0, ?, ?, ?, CURDATE(), ?)"
        );
        return $stmt->execute([$titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu, $id_user]);
    }

    public function updatePublication($id, $titre, $contenu, $categorie = 'general', $image = null, $is_event = 0, $event_date = null, $event_lieu = null) {
        if ($image !== null) {
            $stmt = $this->pdo->prepare(
                "UPDATE forums SET titre=?, contenu=?, categorie=?, image=?, is_event=?, event_date=?, event_lieu=? WHERE id_publication=?"
            );
            return $stmt->execute([$titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu, $id]);
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE forums SET titre=?, contenu=?, categorie=?, is_event=?, event_date=?, event_lieu=? WHERE id_publication=?"
            );
            return $stmt->execute([$titre, $contenu, $categorie, $is_event, $event_date, $event_lieu, $id]);
        }
    }

    public function deletePublication($id) {
        $pub = $this->getPublicationById($id);
        if ($pub && !empty($pub['image'])) {
            $path = __DIR__ . '/../view/frontoffice/assets/img/publications/' . $pub['image'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM forums WHERE id_publication = ?");
        return $stmt->execute([$id]);
    }

    public function incrementerVues($id) {
        $stmt = $this->pdo->prepare("UPDATE forums SET nb_vues = nb_vues + 1 WHERE id_publication = ?");
        return $stmt->execute([$id]);
    }

    public function toggleLike($id_publication, $id_user) {
        $stmt = $this->pdo->prepare("SELECT id FROM publication_likes WHERE id_publication=? AND id_user=?");
        $stmt->execute([$id_publication, $id_user]);
        if ($stmt->fetch()) {
            $this->pdo->prepare("DELETE FROM publication_likes WHERE id_publication=? AND id_user=?")->execute([$id_publication, $id_user]);
            $this->pdo->prepare("UPDATE forums SET nb_likes = GREATEST(nb_likes-1,0) WHERE id_publication=?")->execute([$id_publication]);
            return ['action' => 'unliked'];
        } else {
            $this->pdo->prepare("INSERT INTO publication_likes (id_publication, id_user) VALUES (?,?)")->execute([$id_publication, $id_user]);
            $this->pdo->prepare("UPDATE forums SET nb_likes = nb_likes+1 WHERE id_publication=?")->execute([$id_publication]);
            return ['action' => 'liked'];
        }
    }

    public function hasLiked($id_publication, $id_user) {
        $stmt = $this->pdo->prepare("SELECT id FROM publication_likes WHERE id_publication=? AND id_user=?");
        $stmt->execute([$id_publication, $id_user]);
        return (bool)$stmt->fetch();
    }

    public function uploadImage($file) {
        $uploadDir    = __DIR__ . '/../view/frontoffice/assets/img/publications/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 3 * 1024 * 1024;
        if (!in_array($file['type'], $allowedTypes)) return ['error' => 'Type de fichier non autorisé.'];
        if ($file['size'] > $maxSize) return ['error' => 'Image trop lourde (max 3MB).'];
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('pub_', true) . '.' . strtolower($ext);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return ['error' => "Erreur lors de l'upload."];
        return ['filename' => $filename];
    }

    public function isOwner($id_publication, $id_user) {
        $stmt = $this->pdo->prepare("SELECT id_publication FROM forums WHERE id_publication=? AND id_user=?");
        $stmt->execute([$id_publication, $id_user]);
        return (bool)$stmt->fetch();
    }
}