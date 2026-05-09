<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Event.php';
require_once __DIR__ . '/UserController.php';

class EventController {
    
    public function listEvents() {
        $db = config::getConnexion();
        try {
            $query = $db->query(
                'SELECT
                    e.id_event,
                    e.titre,
                    e.description,
                    e.date_event,
                    e.heure_event,
                    e.lieu,
                    e.capacite,
                    e.id_organisateur,
                    COALESCE(SUM(i.nber_invi), 0) AS nbr_inscri
                 FROM evente e
                 LEFT JOIN inscription i ON e.id_event = i.id_event
                 GROUP BY
                    e.id_event,
                    e.titre,
                    e.description,
                    e.date_event,
                    e.heure_event,
                    e.lieu,
                    e.capacite,
                    e.id_organisateur
                 ORDER BY e.date_event ASC'
            );
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getEventStatistics() {
        UserController::requireAdmin();
        $db = config::getConnexion();
        try {
            $statsQuery = $db->query(
                'SELECT
                    COUNT(*) AS total_events,
                    SUM(CASE WHEN date_event >= CURRENT_DATE() THEN 1 ELSE 0 END) AS upcoming_events
                 FROM evente'
            );
            $stats = $statsQuery->fetch();

            $registrationQuery = $db->query('SELECT COALESCE(SUM(nber_invi), 0) AS total_registrations FROM inscription');
            $registration = $registrationQuery->fetch();

            $popularQuery = $db->query(
                'SELECT
                    e.id_event,
                    e.titre,
                    e.capacite,
                    COALESCE(SUM(i.nber_invi), 0) AS registrations
                 FROM evente e
                 LEFT JOIN inscription i ON e.id_event = i.id_event
                 GROUP BY e.id_event, e.titre, e.capacite
                 ORDER BY registrations DESC
                 LIMIT 1'
            );
            $popularEvent = $popularQuery->fetch();

            return [
                'total_events' => $stats ? (int)$stats['total_events'] : 0,
                'upcoming_events' => $stats ? (int)$stats['upcoming_events'] : 0,
                'total_registrations' => $registration ? (int)$registration['total_registrations'] : 0,
                'popular_event' => $popularEvent ?: null,
            ];
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addEvent($event) {
        UserController::requireAdmin();
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO evente (titre, description, date_event, heure_event, lieu, capacite, id_organisateur, nbr_inscri) 
                VALUES (:titre, :description, :date_event, :heure_event, :lieu, :capacite, :id_organisateur, :nbr_inscri)'
            );
            $query->execute([
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDateEvent(),
                'heure_event' => $event->getHeureEvent(),
                'lieu' => $event->getLieu(),
                'capacite' => $event->getCapacite(),
                'id_organisateur' => $event->getIdOrganisateur(),
                'nbr_inscri' => 0
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function incrementEventRegistrationCount($id) {
        $db = config::getConnexion();
        try {
            $countQuery = $db->prepare('SELECT COALESCE(SUM(nber_invi), 0) AS total FROM inscription WHERE id_event = :id');
            $countQuery->execute(['id' => $id]);
            $result = $countQuery->fetch();
            $count = $result ? (int)$result['total'] : 0;

            $updateQuery = $db->prepare('UPDATE evente SET nbr_inscri = :count WHERE id_event = :id');
            $updateQuery->execute([
                'count' => $count,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteEvent($id) {
        UserController::requireAdmin();
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM evente WHERE id_event = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function updateEvent($event, $id) {
        UserController::requireAdmin();
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE evente SET 
                    titre = :titre, 
                    description = :description, 
                    date_event = :date_event, 
                    heure_event = :heure_event, 
                    lieu = :lieu, 
                    capacite = :capacite, 
                    id_organisateur = :id_organisateur
                WHERE id_event = :id'
            );
            $query->execute([
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDateEvent(),
                'heure_event' => $event->getHeureEvent(),
                'lieu' => $event->getLieu(),
                'capacite' => $event->getCapacite(),
                'id_organisateur' => $event->getIdOrganisateur(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showEvent($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM evente WHERE id_event = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function eventExists(int $id): bool {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT 1 FROM evente WHERE id_event = :id LIMIT 1');
            $query->execute(['id' => $id]);
            return (bool)$query->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkOrganizerExists($id_organisateur) {
        $db = config::getConnexion();
        try {
            // Try checking in different possible table names
            $tables = ['utilisateur', 'user', 'users', 'organisateur', 'organisateurs'];
            
            foreach ($tables as $table) {
                try {
                    $query = $db->prepare("SELECT id FROM $table WHERE id = :id LIMIT 1");
                    $query->execute(['id' => $id_organisateur]);
                    $result = $query->fetch();
                    if ($result) {
                        return true;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
