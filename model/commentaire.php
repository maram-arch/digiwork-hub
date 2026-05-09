<?php
// model/Commentaire.php
require_once __DIR__ . '/../config/config.php';

class Commentaire
{
    private $id_commentaire;
    private $contenu;
    private $id_publication;
    private $id_users;        // propriété PHP (nom inchangé mais colonne SQL = id_user)
    private $parent_id;
    private $date_commentaire;

    public function __construct($id = null, $contenu = null, $id_publication = null, $id_users = null, $parent_id = null) {
        if ($id !== null) $this->id_commentaire = $id;
        if ($contenu !== null) $this->contenu = $contenu;
        if ($id_publication !== null) $this->id_publication = $id_publication;
        if ($id_users !== null) $this->id_users = $id_users;
        if ($parent_id !== null) $this->parent_id = $parent_id;
    }

    public function getContenu() { return $this->contenu; }
    public function setContenu($contenu) { $this->contenu = $contenu; }
    public function getId_publication() { return $this->id_publication; }
    public function getId_users() { return $this->id_users; }

    public static function getByPublication($id_publication) {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = c.id_commentaire) as total_likes
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                WHERE c.id_publication = :id_pub AND (c.parent_id IS NULL OR c.parent_id = 0)
                ORDER BY c.date_commentaire ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_pub' => $id_publication]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTreeByPublication($id_publication) {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = c.id_commentaire) as total_likes
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                WHERE c.id_publication = :id_pub
                ORDER BY c.date_commentaire ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_pub' => $id_publication]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $children = [];
        foreach ($comments as $c) {
            $parent = $c['parent_id'] ?? 0;
            $children[$parent][] = $c;
        }
        $buildTree = function($parentId = 0) use (&$buildTree, $children) {
            $branch = [];
            if (isset($children[$parentId])) {
                foreach ($children[$parentId] as $child) {
                    $child['reponses'] = $buildTree($child['id_commentaire']);
                    $branch[] = $child;
                }
            }
            return $branch;
        };
        return $buildTree(0);
    }

    public static function add($id_publication, $id_users, $contenu, $parent_id = null) {
        $pdo = Config::getConnexion();
        $sql = "INSERT INTO commentaire (id_publication, id_user, contenu, parent_id, date_commentaire)
                VALUES (:id_pub, :id_user, :contenu, :parent_id, NOW())";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_pub' => $id_publication,
            ':id_user' => $id_users,
            ':contenu' => $contenu,
            ':parent_id' => $parent_id
        ]);
    }

    public static function toggleLike($id_commentaire, $id_users) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = :id_c AND id_user = :id_u");
        $stmt->execute([':id_c' => $id_commentaire, ':id_u' => $id_users]);
        $exists = $stmt->fetchColumn() > 0;
        if ($exists) {
            $pdo->prepare("DELETE FROM commentaire_likes WHERE id_commentaire = :id_c AND id_user = :id_u")->execute([':id_c' => $id_commentaire, ':id_u' => $id_users]);
            $action = 'unliked';
        } else {
            $pdo->prepare("INSERT INTO commentaire_likes (id_commentaire, id_user) VALUES (:id_c, :id_u)")->execute([':id_c' => $id_commentaire, ':id_u' => $id_users]);
            $action = 'liked';
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = :id_c");
        $stmt->execute([':id_c' => $id_commentaire]);
        $nb_likes = (int)$stmt->fetchColumn();
        return ['action' => $action, 'nb_likes' => $nb_likes];
    }

    public static function hasLiked($id_commentaire, $id_users) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = :id_c AND id_user = :id_u");
        $stmt->execute([':id_c' => $id_commentaire, ':id_u' => $id_users]);
        return $stmt->fetchColumn() > 0;
    }

    public static function getCommentairesByUsersWithPublication($id_users) {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, f.titre as publication_titre, f.id_publication
                FROM commentaire c
                LEFT JOIN forums f ON c.id_publication = f.id_publication
                WHERE c.id_user = :id_users
                ORDER BY c.date_commentaire DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_users' => $id_users]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCommentaire() {
        $pdo = Config::getConnexion();
        $sql = "INSERT INTO commentaire (id_publication, id_user, contenu, parent_id, date_commentaire)
                VALUES (:id_pub, :id_user, :contenu, :parent_id, NOW())";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_pub' => $this->id_publication,
            ':id_user' => $this->id_users,
            ':contenu' => $this->contenu,
            ':parent_id' => $this->parent_id
        ]);
    }

    public function deleteCommentaire($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
        return $stmt->execute([$id]);
    }

    public function updateCommentaire($id, $contenu) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE commentaire SET contenu = ? WHERE id_commentaire = ?");
        return $stmt->execute([$contenu, $id]);
    }

    public static function addReponse($contenu, $id_publication, $id_users, $parent_id) {
        $pdo = Config::getConnexion();
        $sql = "INSERT INTO commentaire (id_publication, id_user, contenu, parent_id, date_commentaire)
                VALUES (:id_pub, :id_user, :contenu, :parent_id, NOW())";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_pub' => $id_publication,
            ':id_user' => $id_users,
            ':contenu' => $contenu,
            ':parent_id' => $parent_id
        ]);
    }

    public static function getAllCommentairesWithUsersAndPublication() {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom, f.titre as publication_titre
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                LEFT JOIN forums f ON c.id_publication = f.id_publication
                ORDER BY c.date_commentaire DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCommentairesByPublicationWithUsers($id_publication) {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                WHERE c.id_publication = ?
                ORDER BY c.date_commentaire ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_publication]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCommentaireByIdWithUsers($id_commentaire) {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom, f.titre as publication_titre
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                LEFT JOIN forums f ON c.id_publication = f.id_publication
                WHERE c.id_commentaire = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_commentaire]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getNbCommentairesParPublication() {
        $pdo = Config::getConnexion();
        $sql = "SELECT id_publication, COUNT(*) as nb_commentaires
                FROM commentaire
                GROUP BY id_publication";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>