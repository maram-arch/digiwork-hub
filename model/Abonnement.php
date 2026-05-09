<?php
require_once __DIR__ . '/../config/config.php';

class Abonnement {
    private ?int $id_abonnement;
    private ?int $id_user;
    private ?string $date_deb;
    private ?string $date_fin;
    private ?string $status;

    public function __construct(?int $id_abonnement = null, ?int $id_user = null, ?string $date_deb = null, ?string $date_fin = null, ?string $status = null) {
        $this->id_abonnement = $id_abonnement;
        $this->id_user = $id_user;
        $this->date_deb = $date_deb;
        $this->date_fin = $date_fin;
        $this->status = $status;
    }

    // Getters
    public function getIdAbonnement(): ?int { return $this->id_abonnement; }
    public function getIdUser(): ?int { return $this->id_user; }
    public function getDateDeb(): ?string { return $this->date_deb; }
    public function getDateFin(): ?string { return $this->date_fin; }
    public function getStatus(): ?string { return $this->status; }

    // Setters
    public function setIdAbonnement(?int $id_abonnement): void { $this->id_abonnement = $id_abonnement; }
    public function setIdUser(?int $id_user): void { $this->id_user = $id_user; }
    public function setDateDeb(?string $date_deb): void { $this->date_deb = $date_deb; }
    public function setDateFin(?string $date_fin): void { $this->date_fin = $date_fin; }
    public function setStatus(?string $status): void { $this->status = $status; }

    // Database methods
    public function getAll() {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT * FROM abonnement ORDER BY `date-deb` DESC');
            return $query;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM abonnement WHERE `id-abonnement` = ?');
            $query->execute([$id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getByUserId($userId) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM abonnement WHERE `id-user` = ? ORDER BY `date-deb` DESC');
            $query->execute([$userId]);
            return $query;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function create() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO abonnement (`id-user`, `date-deb`, `date-fin`, status)
                VALUES (:id_user, :date_deb, :date_fin, :status)'
            );
            $query->execute([
                'id_user' => $this->id_user,
                'date_deb' => $this->date_deb,
                'date_fin' => $this->date_fin,
                'status' => $this->status
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function update() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE abonnement SET `id-user` = :id_user, `date-deb` = :date_deb,
                `date-fin` = :date_fin, status = :status WHERE `id-abonnement` = :id_abonnement'
            );
            $query->execute([
                'id_user' => $this->id_user,
                'date_deb' => $this->date_deb,
                'date_fin' => $this->date_fin,
                'status' => $this->status,
                'id_abonnement' => $this->id_abonnement
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function delete() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM abonnement WHERE `id-abonnement` = ?');
            $query->execute([$this->id_abonnement]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}