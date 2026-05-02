<?php
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