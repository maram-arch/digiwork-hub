<?php

require_once __DIR__ . '/../config2.php';
require_once __DIR__ . '/../model/project.php';

class ProjetC
{
 public function listProjets($sort = 'id-projet', $direction = 'ASC', $searchId = '')
{
    $db = config::getConnexion();

    $allowedSorts = ['id-projet', 'titre', 'budget', 'statut', 'id-user', 'id-offre'];
    $allowedDirections = ['ASC', 'DESC'];

    if (!in_array($sort, $allowedSorts)) {
        $sort = 'id-projet';
    }

    $direction = strtoupper($direction);

    if (!in_array($direction, $allowedDirections)) {
        $direction = 'ASC';
    }

    if (!empty($searchId)) {
        $sql = "SELECT * FROM projet 
                WHERE `id-projet` = :searchId
                ORDER BY `$sort` $direction";

        $query = $db->prepare($sql);
        $query->execute(['searchId' => $searchId]);
        return $query;
    }

    $sql = "SELECT * FROM projet ORDER BY `$sort` $direction";
    return $db->query($sql);
}


public function listSponsors($sort = 'id_user', $direction = 'ASC', $searchIdUser = '')
{
    $db = config::getConnexion();

    $allowedSorts = ['id_user', 'nom', 'type'];
    $allowedDirections = ['ASC', 'DESC'];

    if (!in_array($sort, $allowedSorts)) {
        $sort = 'id_user';
    }

    $direction = strtoupper($direction);

    if (!in_array($direction, $allowedDirections)) {
        $direction = 'ASC';
    }

    if (!empty($searchIdUser)) {
        $sql = "SELECT * FROM sponsor 
                WHERE id_user = :searchIdUser
                ORDER BY `$sort` $direction";

        $query = $db->prepare($sql);
        $query->execute(['searchIdUser' => $searchIdUser]);
        return $query;
    }

    $sql = "SELECT * FROM sponsor ORDER BY `$sort` $direction";
    return $db->query($sql);
}
public function getProjectStatsByStatus()
{
    $db = config::getConnexion();

    $sql = "SELECT statut, COUNT(*) AS total
            FROM projet
            GROUP BY statut";

    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

public function getMostSponsoredStats()
{
    $db = config::getConnexion();

    $sql = "SELECT nom, COUNT(*) AS total
            FROM sponsor
            GROUP BY nom
            ORDER BY total DESC";

    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
    public function getProjetById($id)
    {
        $sql = "SELECT * FROM projet WHERE `id-projet` = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function addProjet($projet)
    {
        $sql = "INSERT INTO projet (`titre`, `discription`, `budget`, `statut`, `id-user`, `id-offre`)
                VALUES (:titre, :discription, :budget, :statut, :id_user, :id_offre)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $projet->getTitre(),
                'discription' => $projet->getDiscription(),
                'budget' => $projet->getBudget(),
                'statut' => $projet->getStatut(),
                'id_user' => $projet->getIdUser(),
                'id_offre' => $projet->getIdOffre()
            ]);
        } catch (Exception $e) {
            die('Erreur addProjet : ' . $e->getMessage());
        }
    }

    public function updateProjet($projet, $id)
    {
        $sql = "UPDATE projet SET
                    `titre` = :titre,
                    `discription` = :discription,
                    `budget` = :budget,
                    `statut` = :statut,
                    `id-user` = :id_user,
                    `id-offre` = :id_offre
                WHERE `id-projet` = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'titre' => $projet->getTitre(),
                'discription' => $projet->getDiscription(),
                'budget' => $projet->getBudget(),
                'statut' => $projet->getStatut(),
                'id_user' => $projet->getIdUser(),
                'id_offre' => $projet->getIdOffre()
            ]);
        } catch (Exception $e) {
            die('Erreur updateProjet : ' . $e->getMessage());
        }
    }

    public function deleteProjet($id)
    {
        $sql = "DELETE FROM projet WHERE `id-projet` = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur deleteProjet : ' . $e->getMessage());
        }
    }
   public function listProjetsLimited($limit = 3)
{
    $db = config::getConnexion();

    $sql = "SELECT * FROM projet ORDER BY `id-projet` DESC LIMIT :limit";
    $query = $db->prepare($sql);
    $query->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $query->execute();

    return $query;
}
}
?>