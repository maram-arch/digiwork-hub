<?php

require_once __DIR__ . '/../model/Publication.php';
require_once __DIR__ . '/../config/config.php';

class PublicationController
{
    public function addPublication($publication)
    {
        return $publication->addPublication();
    }

    public function getPublication($id)
    {
        return Publication::getByIdWithUser($id);
    }

    public function updatePublication($id, $titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu)
    {
        $db = Config::getConnexion();
        $sql = "UPDATE forums 
                SET titre=?, contenu=?, categorie=?, image=?, is_event=?, event_date=?, event_lieu=? 
                WHERE id_publication=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu, $id]);
    }

    public function deletePublication($id)
    {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM forums WHERE id_publication = ?");
        return $stmt->execute([$id]);
    }

    public function listPublication()
    {
        // Correction : table 'user' et colonne 'id_user'
        $sql = "SELECT f.*, u.nom, u.prenom
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                ORDER BY f.date_publication DESC";
        return Config::getConnexion()->query($sql);
    }
}
?>