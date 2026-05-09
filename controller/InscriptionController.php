<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Inscription.php';
require_once __DIR__ . '/EventController.php';
require_once __DIR__ . '/UserController.php';

class InscriptionController {

    public function listInscriptions($eventId = null) {
        // Admin check is handled by the calling page (backoffice)
        $db = config::getConnexion();
        try {
            if ($eventId !== null && $eventId > 0) {
                $query = $db->prepare(
                    'SELECT i.*, e.titre AS event_title, u.email AS user_email
                     FROM inscription i
                     LEFT JOIN evente e ON i.id_event = e.id_event
                     LEFT JOIN `user` u ON i.id_user = u.id_user
                     WHERE i.id_event = :event_id'
                );
                $query->execute(['event_id' => $eventId]);
            } else {
                $query = $db->query(
                    'SELECT i.*, e.titre AS event_title, u.email AS user_email
                     FROM inscription i
                     LEFT JOIN evente e ON i.id_event = e.id_event
                     LEFT JOIN `user` u ON i.id_user = u.id_user'
                );
            }
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getTotalInvitesForEvent(int $eventId, int $excludeInscriptionId = null): int {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT COALESCE(SUM(nber_invi), 0) AS total FROM inscription WHERE id_event = :event_id';
            if ($excludeInscriptionId !== null) {
                $sql .= ' AND id_inscription != :exclude_id';
            }
            $query = $db->prepare($sql);
            $params = ['event_id' => $eventId];
            if ($excludeInscriptionId !== null) {
                $params['exclude_id'] = $excludeInscriptionId;
            }
            $query->execute($params);
            $result = $query->fetch();
            return $result ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addInscription($inscription) {
        // Auth check: use session user_id if available, otherwise allow guest inscription
        $db = config::getConnexion();
        try {
            $eventController = new EventController();
            if (!$eventController->eventExists($inscription->getIdEvent())) {
                throw new Exception('Événement introuvable.');
            }

            $event = $eventController->showEvent($inscription->getIdEvent());
            if (!$event) {
                throw new Exception('Événement introuvable.');
            }
            $capacity = isset($event['capacite']) ? (int)$event['capacite'] : 0;
            $currentInvites = $this->getTotalInvitesForEvent($inscription->getIdEvent());
            if ($currentInvites + (int)$inscription->getNberInvi() > $capacity) {
                throw new Exception('La capacité de l\'événement est dépassée. Il reste seulement ' . max(0, $capacity - $currentInvites) . ' place(s).');
            }

            $query = $db->prepare(
                'INSERT INTO inscription (nom, post, nber_invi, id_user, id_event) 
                VALUES (:nom, :post, :nber_invi, :id_user, :id_event)'
            );
            $query->execute([
                'nom' => $inscription->getNom(),
                'post' => $inscription->getPost(),
                'nber_invi' => $inscription->getNberInvi(),
                'id_user' => $inscription->getIdUser(),
                'id_event' => $inscription->getIdEvent()
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function sendEventEmail(string $fromEmail, string $recipient, string $subject, string $message): bool {
        $headers = 'From: ' . $fromEmail . "\r\n" .
                   'Reply-To: ' . $fromEmail . "\r\n" .
                   'MIME-Version: 1.0' . "\r\n" .
                   'Content-type: text/plain; charset=utf-8';

        return mail($recipient, $subject, $message, $headers);
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
        UserController::requireAdmin();
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
        UserController::requireAdmin();
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

            $event = $eventController->showEvent($newEventId);
            if (!$event) {
                throw new Exception('Événement introuvable.');
            }
            $capacity = isset($event['capacite']) ? (int)$event['capacite'] : 0;
            $currentInvites = $this->getTotalInvitesForEvent($newEventId, $id);
            if ($currentInvites + (int)$inscription->getNberInvi() > $capacity) {
                throw new Exception('La capacité de l\'événement est dépassée. Il reste seulement ' . max(0, $capacity - $currentInvites) . ' place(s).');
            }

            $query = $db->prepare(
                'UPDATE inscription SET
                    nom = :nom,
                    post = :post,
                    nber_invi = :nber_invi,
                    id_user = :id_user,
                    id_event = :id_event
                WHERE id_inscription = :id'
            );
            $query->execute([
                'nom' => $inscription->getNom(),
                'post' => $inscription->getPost(),
                'nber_invi' => $inscription->getNberInvi(),
                'id_user' => $inscription->getIdUser(),
                'id_event' => $newEventId,
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
        UserController::requireAdmin();
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
