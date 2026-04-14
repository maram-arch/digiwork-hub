<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Event.php';

class EventController {
    
    public function listEvents() {
        $db = config::getConnexion();
        try {
            $query = $db->query(
                'SELECT * FROM evente'
            );
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addEvent($event) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO evente (titre, description, date_event, heure_event, lieu, capacite, id_organisateur) 
                VALUES (:titre, :description, :date_event, :heure_event, :lieu, :capacite, :id_organisateur)'
            );
            $query->execute([
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDateEvent(),
                'heure_event' => $event->getHeureEvent(),
                'lieu' => $event->getLieu(),
                'capacite' => $event->getCapacite(),
                'id_organisateur' => $event->getIdOrganisateur()
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    public function deleteEvent($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM evente WHERE id_event = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function updateEvent($event, $id) {
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
