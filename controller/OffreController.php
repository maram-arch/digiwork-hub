<?php
require_once __DIR__ . '/../config/config.php';
 
class OffreController
{
    // ── ADD ──────────────────────────────────────────────────
    public function addOffre($offre)
    {
        $sql = "INSERT INTO offre
                VALUES (NULL, :titre, :description, :competences,
                        :date_limite, :adresse, :type, :id_entreprise)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'         => $offre->getTitre(),
                'description'   => $offre->getDescription(),
                'competences'   => $offre->getCompetences(),
                'date_limite'   => $offre->getDateLimite(),
                'adresse'       => $offre->getAdresse(),
                'type'          => $offre->getType(),
                'id_entreprise' => $offre->getIdEntreprise(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
 
    // ── LIST ─────────────────────────────────────────────────
    public function listOffre()
    {
        $sql = "SELECT * FROM offre ORDER BY id_offer DESC";
        $db  = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
 
    // ── GET ONE ──────────────────────────────────────────────
    public function getOffre($id)
    {
        $sql = "SELECT * FROM offre WHERE id_offer = :id";
        $db  = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $offre = $query->fetch(PDO::FETCH_ASSOC);
            return $offre;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
 
    // ── UPDATE ───────────────────────────────────────────────
    public function updateOffre($offre, $id)
    {
        try {
            $db    = Config::getConnexion();
            $query = $db->prepare(
                'UPDATE offre SET
                    titre         = :titre,
                    description   = :description,
                    competences   = :competences,
                    date_limite   = :date_limite,
                    adresse       = :adresse,
                    type          = :type,
                    id_entreprise = :id_entreprise
                WHERE id_offer = :id'
            );
            $query->execute([
                'id'            => $id,
                'titre'         => $offre->getTitre(),
                'description'   => $offre->getDescription(),
                'competences'   => $offre->getCompetences(),
                'date_limite'   => $offre->getDateLimite(),
                'adresse'       => $offre->getAdresse(),
                'type'          => $offre->getType(),
                'id_entreprise' => $offre->getIdEntreprise(),
            ]);
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
    }
 
    // ── DELETE ───────────────────────────────────────────────
    public function deleteOffre($id)
    {
        $sql = "DELETE FROM offre WHERE id_offer = :id";
        $db  = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
 