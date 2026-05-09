<?php
class PublicationModel {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../config.php';
        $this->pdo = getConnection();
    }

    public function getAllPublications() {
        $stmt = $this->pdo->query("SELECT * FROM forums ORDER BY date_publication DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPublications() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM forums");
        return $stmt->fetchColumn();
    }

    public function getPublicationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM forums WHERE id_publication = ?");
        $stmt->execute(array($id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addPublication($titre, $contenu, $id_user) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO forums (titre, contenu, date_publication, id_user) 
             VALUES (?, ?, CURDATE(), ?)"
        );
        return $stmt->execute(array($titre, $contenu, $id_user));
    }

    public function updatePublication($id, $titre, $contenu) {
        $stmt = $this->pdo->prepare(
            "UPDATE forums SET titre = ?, contenu = ? WHERE id_publication = ?"
        );
        return $stmt->execute(array($titre, $contenu, $id));
    }

    public function deletePublication($id) {
        $stmt = $this->pdo->prepare("DELETE FROM forums WHERE id_publication = ?");
        return $stmt->execute(array($id));
    }
}