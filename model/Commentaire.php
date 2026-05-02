<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php'; // pour Twilio

use Twilio\Rest\Client;

class Commentaire
{
    private $pdo;
    private $id_commentaire;
    private $contenu;
    private $date_commentaire;
    private $id_publication;
    private $id_user;
    private $parent_id;

    // Constructeur
    public function __construct($id_commentaire = 0, $contenu = '', $id_publication = 0, $id_user = 0, $parent_id = null)
    {
        $this->pdo = Config::getConnexion();
        $this->id_commentaire = $id_commentaire;
        $this->contenu = $contenu;
        $this->date_commentaire = date('Y-m-d H:i:s');
        $this->id_publication = $id_publication;
        $this->id_user = $id_user;
        $this->parent_id = $parent_id;
    }

    // Getters / Setters
    public function getIdCommentaire() { return $this->id_commentaire; }
    public function getContenu()       { return $this->contenu; }
    public function getDateCommentaire(){ return $this->date_commentaire; }
    public function getIdPublication() { return $this->id_publication; }
    public function getIdUser()        { return $this->id_user; }
    public function getParentId()      { return $this->parent_id; }

    public function setContenu(string $c)          { $this->contenu = $c; }
    public function setDateCommentaire(string $d)  { $this->date_commentaire = $d; }
    public function setIdPublication(int $id)      { $this->id_publication = $id; }
    public function setIdUser(int $id)             { $this->id_user = $id; }
    public function setParentId($id)               { $this->parent_id = $id; }

    // ========== AJOUT D'UN COMMENTAIRE (instance) ==========
    public function addCommentaire(): bool
    {
        $sql = "INSERT INTO commentaire (contenu, date_commentaire, id_publication, id_user, parent_id, nb_likes) 
                VALUES (?, NOW(), ?, ?, ?, 0)";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([
            $this->contenu,
            $this->id_publication,
            $this->id_user,
            $this->parent_id
        ]);

        if ($success) {
            // Envoi WhatsApp direct (identique au test réussi)
            $stmt2 = $this->pdo->prepare("
                SELECT u.tel 
                FROM user u
                JOIN forums f ON u.id_user = f.id_user
                WHERE f.id_publication = ?
            ");
            $stmt2->execute([$this->id_publication]);
            $tel = $stmt2->fetchColumn();

            // Ne pas notifier si le numéro est invalide ou si l'interacteur est le propriétaire
            if ($tel && $tel != '2147483647' && $tel != $this->id_user) {
                $client = new Client(Config::TWILIO_ACCOUNT_SID, Config::TWILIO_AUTH_TOKEN);
                $to = 'whatsapp:+' . $tel;
                $from = 'whatsapp:' . Config::TWILIO_WHATSAPP_NUMBER;
                $body = "💬 Nouveau commentaire sur votre publication.";
                try {
                    $client->messages->create($to, ['from' => $from, 'body' => $body]);
                    error_log("WhatsApp envoyé à $tel");
                } catch (Exception $e) {
                    error_log("Erreur WhatsApp: " . $e->getMessage());
                }
            }
        }
        return $success;
    }

    // ========== MÉTHODES STATIQUES ==========
    public static function getTreeByPublication($id_publication): array
    {
        $pdo = Config::getConnexion();
        $sql = "SELECT c.*, u.nom, u.prenom,
                (SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = c.id_commentaire) as total_likes
                FROM commentaire c
                LEFT JOIN user u ON c.id_user = u.id_user
                WHERE c.id_publication = ?
                ORDER BY c.date_commentaire ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_publication]);
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return self::buildTree($commentaires);
    }

    private static function buildTree($elements, $parentId = null) {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id_commentaire']);
                if ($children) $element['reponses'] = $children;
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public static function getByPublication($id_publication): array
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT c.*, u.nom, u.prenom
            FROM commentaire c
            LEFT JOIN user u ON c.id_user = u.id_user
            WHERE c.id_publication = ?
            ORDER BY c.date_commentaire ASC
        ");
        $stmt->execute([$id_publication]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addReponse($contenu, $id_publication, $id_user, $parent_id): bool
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO commentaire (contenu, date_commentaire, id_publication, id_user, parent_id) 
                               VALUES (?, NOW(), ?, ?, ?)");
        $success = $stmt->execute([$contenu, $id_publication, $id_user, $parent_id]);
        if ($success) {
            // Envoi WhatsApp direct (identique à addCommentaire)
            $stmt2 = $pdo->prepare("
                SELECT u.tel 
                FROM user u
                JOIN forums f ON u.id_user = f.id_user
                WHERE f.id_publication = ?
            ");
            $stmt2->execute([$id_publication]);
            $tel = $stmt2->fetchColumn();
            if ($tel && $tel != '2147483647' && $tel != $id_user) {
                $client = new Client(Config::TWILIO_ACCOUNT_SID, Config::TWILIO_AUTH_TOKEN);
                $to = 'whatsapp:+' . $tel;
                $from = 'whatsapp:' . Config::TWILIO_WHATSAPP_NUMBER;
                $body = "💬 Nouvelle réponse sur votre publication.";
                try {
                    $client->messages->create($to, ['from' => $from, 'body' => $body]);
                } catch (Exception $e) {}
            }
        }
        return $success;
    }

    public static function toggleLikeCommentaire($id_commentaire, $id_user): array
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM commentaire_likes WHERE id_commentaire = ? AND id_user = ?");
        $stmt->execute([$id_commentaire, $id_user]);
        if ($stmt->fetchColumn()) {
            $pdo->prepare("DELETE FROM commentaire_likes WHERE id_commentaire = ? AND id_user = ?")->execute([$id_commentaire, $id_user]);
            $action = 'unliked';
        } else {
            $pdo->prepare("INSERT INTO commentaire_likes (id_commentaire, id_user) VALUES (?, ?)")->execute([$id_commentaire, $id_user]);
            $action = 'liked';
        }
        $nbStmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = ?");
        $nbStmt->execute([$id_commentaire]);
        $nb_likes = (int)$nbStmt->fetchColumn();
        return ['action' => $action, 'nb_likes' => $nb_likes];
    }

    public function deleteCommentaire(int $id_commentaire): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
        return $stmt->execute([$id_commentaire]);
    }

    public function updateCommentaire(int $id_commentaire, string $contenu): bool
    {
        $stmt = $this->pdo->prepare("UPDATE commentaire SET contenu = ? WHERE id_commentaire = ?");
        return $stmt->execute([$contenu, $id_commentaire]);
    }

    // ========== MÉTHODES DE JOINTURE ==========
    public function getAllCommentairesWithUserAndPublication(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.nom, u.prenom, p.titre AS titre_publication
                FROM commentaire c
                INNER JOIN user u ON c.id_user = u.id_user
                INNER JOIN forums p ON c.id_publication = p.id_publication
                ORDER BY c.date_commentaire DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCommentairesByUserWithPublication(int $id_user): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, p.titre AS titre_publication, p.categorie
                FROM commentaire c
                INNER JOIN forums p ON c.id_publication = p.id_publication
                WHERE c.id_user = :id_user
                ORDER BY c.date_commentaire DESC
            ");
            $stmt->execute([':id_user' => $id_user]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCommentairesByPublicationWithUser(int $id_publication): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.nom, u.prenom
                FROM commentaire c
                INNER JOIN user u ON c.id_user = u.id_user
                WHERE c.id_publication = :id_publication
                ORDER BY c.date_commentaire ASC
            ");
            $stmt->execute([':id_publication' => $id_publication]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCommentaireByIdWithUser(int $id_commentaire): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.nom, u.prenom, p.titre AS titre_publication
                FROM commentaire c
                INNER JOIN user u ON c.id_user = u.id_user
                INNER JOIN forums p ON c.id_publication = p.id_publication
                WHERE c.id_commentaire = :id_commentaire
            ");
            $stmt->execute([':id_commentaire' => $id_commentaire]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getNbCommentairesParPublication(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.id_publication, p.titre, COUNT(c.id_commentaire) AS nb_commentaires
                FROM forums p
                LEFT JOIN commentaire c ON p.id_publication = c.id_publication
                GROUP BY p.id_publication, p.titre
                ORDER BY nb_commentaires DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>