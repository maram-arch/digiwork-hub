<?php

require_once __DIR__ . '/../config2.php';
require_once __DIR__ . '/../model/project.php';

class ProjetC
{
    public function listProjets()
    {
        $sql = "SELECT * FROM projet ORDER BY `id-projet` DESC";
        $db = config::getConnexion();
        return $db->query($sql);
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
}
?>