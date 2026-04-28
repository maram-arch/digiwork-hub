<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Inscription.php';
require_once __DIR__ . '/EventController.php';

class InscriptionController {
    
    public function listInscriptions() {
        $db = config::getConnexion();
        try {
            $query = $db->query(
                'SELECT i.*, e.titre AS event_title, u.email AS user_email
                 FROM inscription i
                 LEFT JOIN evente e ON i.id_event = e.id_event
                 LEFT JOIN `user` u ON i.id_user = u.id_user'
            );
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addInscription($inscription) {
        $db = config::getConnexion();
        try {
            $eventController = new EventController();
            if (!$eventController->eventExists($inscription->getIdEvent())) {
                throw new Exception('Événement introuvable.');
            }

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

    public function userExists(int $id_user): bool {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT 1 FROM `user` WHERE id_user = :id_user LIMIT 1');
            $query->execute(['id_user' => $id_user]);
            return (bool)$query->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function createPlaceholderUser(int $id_user): bool {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO `user` (id_user, email, mdp, tel) VALUES (:id_user, :email, :mdp, :tel)');
            $query->execute([
                'id_user' => $id_user,
                'email' => 'inconnu_' . $id_user . '@example.com',
                'mdp' => 'changeme',
                'tel' => 0
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteInscription($id) {
        $db = config::getConnexion();
        try {
            $oldQuery = $db->prepare('SELECT id_event FROM inscription WHERE id_inscription = :id');
            $oldQuery->execute(['id' => $id]);
            $oldRow = $oldQuery->fetch();
            $oldEventId = $oldRow ? (int)$oldRow['id_event'] : null;

            $query = $db->prepare('DELETE FROM inscription WHERE id_inscription = :id');
            $query->execute(['id' => $id]);
            $deleted = $query->rowCount() > 0;

            if ($deleted && $oldEventId) {
                $eventController = new EventController();
                $eventController->incrementEventRegistrationCount($oldEventId);
            }

            return $deleted;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function updateInscription($inscription, $id) {
        $db = config::getConnexion();
        try {
            $oldQuery = $db->prepare('SELECT id_event FROM inscription WHERE id_inscription = :id');
            $oldQuery->execute(['id' => $id]);
            $oldRow = $oldQuery->fetch();
            $oldEventId = $oldRow ? (int)$oldRow['id_event'] : null;
            $newEventId = $inscription->getIdEvent();

            $eventController = new EventController();
            if (!$eventController->eventExists($newEventId)) {
                throw new Exception('Événement introuvable.');
            }

            $query = $db->prepare(
                'UPDATE inscription SET 
                    id_user = :id_user, 
                    id_event = :id_event, 
                    statut = :statut
                WHERE id_inscription = :id'
            );
            $query->execute([
                'id_user' => $inscription->getIdUser(),
                'id_event' => $newEventId,
                'statut' => $inscription->getStatut(),
                'id' => $id
            ]);

            if ($oldEventId && $oldEventId !== $newEventId) {
                $eventController->incrementEventRegistrationCount($oldEventId);
            }
            if ($newEventId) {
                $eventController->incrementEventRegistrationCount($newEventId);
            }
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
