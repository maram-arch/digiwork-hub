<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Inscription.php';

class InscriptionController {
    
    public function listInscriptions() {
        $db = config::getConnexion();
        try {
            $query = $db->query(
                'SELECT * FROM inscription'
            );
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addInscription($inscription) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO inscription (id_user, id_event, statut) 
                VALUES (:id_user, :id_event, :statut)'
            );
            $query->execute([
                'id_user' => $inscription->getIdUser(),
                'id_event' => $inscription->getIdEvent(),
                'statut' => $inscription->getStatut()
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    public function deleteInscription($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM inscription WHERE id_inscription = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function updateInscription($inscription, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE inscription SET 
                    id_user = :id_user, 
                    id_event = :id_event, 
                    statut = :statut
                WHERE id_inscription = :id'
            );
            $query->execute([
                'id_user' => $inscription->getIdUser(),
                'id_event' => $inscription->getIdEvent(),
                'statut' => $inscription->getStatut(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showInscription($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM inscription WHERE id_inscription = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
