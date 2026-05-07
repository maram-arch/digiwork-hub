<?php
require_once 'config/config.php';

class PackEvent {
    private ?int $id_pack_event;
    private ?int $id_pack;
    private ?int $id_event;
    private ?string $statut;
    private ?string $date_creation;

    public function __construct(?int $id_pack_event = null, ?int $id_pack = null, ?int $id_event = null, ?string $statut = 'actif', ?string $date_creation = null) {
        $this->id_pack_event = $id_pack_event;
        $this->id_pack = $id_pack;
        $this->id_event = $id_event;
        $this->statut = $statut;
        $this->date_creation = $date_creation;
    }

    // Getters
    public function getIdPackEvent(): ?int { return $this->id_pack_event; }
    public function getIdPack(): ?int { return $this->id_pack; }
    public function getIdEvent(): ?int { return $this->id_event; }
    public function getStatut(): ?string { return $this->statut; }
    public function getDateCreation(): ?string { return $this->date_creation; }

    // Setters
    public function setIdPackEvent(?int $id_pack_event): void { $this->id_pack_event = $id_pack_event; }
    public function setIdPack(?int $id_pack): void { $this->id_pack = $id_pack; }
    public function setIdEvent(?int $id_event): void { $this->id_event = $id_event; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
    public function setDateCreation(?string $date_creation): void { $this->date_creation = $date_creation; }

    // CRUD Operations
    public function create(): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "INSERT INTO pack_event (id_pack, id_event, statut) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->id_pack, $this->id_event, $this->statut]);
        } catch (PDOException $e) {
            error_log("Erreur création pack_event: " . $e->getMessage());
            return false;
        }
    }

    public static function getById(int $id): ?PackEvent {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT * FROM pack_event WHERE id_pack_event = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            
            if ($row) {
                return new PackEvent(
                    $row['id_pack_event'],
                    $row['id_pack'],
                    $row['id_event'],
                    $row['statut'],
                    $row['date_creation']
                );
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur getById pack_event: " . $e->getMessage());
            return null;
        }
    }

    public static function getAll(): array {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT pe.*, p.nom_pack, e.titre as event_titre 
                    FROM pack_event pe 
                    LEFT JOIN pack p ON pe.id_pack = p.id_pack 
                    LEFT JOIN evente e ON pe.id_event = e.id_event 
                    ORDER BY pe.date_creation DESC";
            $stmt = $pdo->query($sql);
            $packEvents = [];
            
            while ($row = $stmt->fetch()) {
                $packEvents[] = $row;
            }
            return $packEvents;
        } catch (PDOException $e) {
            error_log("Erreur getAll pack_event: " . $e->getMessage());
            return [];
        }
    }

    public static function getByPack(int $id_pack): array {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT pe.*, e.titre, e.date_event, e.lieu 
                    FROM pack_event pe 
                    LEFT JOIN evente e ON pe.id_event = e.id_event 
                    WHERE pe.id_pack = ? AND pe.statut = 'actif'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_pack]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByPack pack_event: " . $e->getMessage());
            return [];
        }
    }

    public static function getByEvent(int $id_event): array {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT pe.*, p.nom_pack, p.prix 
                    FROM pack_event pe 
                    LEFT JOIN pack p ON pe.id_pack = p.id_pack 
                    WHERE pe.id_event = ? AND pe.statut = 'actif'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_event]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByEvent pack_event: " . $e->getMessage());
            return [];
        }
    }

    public function update(): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "UPDATE pack_event SET id_pack = ?, id_event = ?, statut = ? WHERE id_pack_event = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->id_pack, $this->id_event, $this->statut, $this->id_pack_event]);
        } catch (PDOException $e) {
            error_log("Erreur update pack_event: " . $e->getMessage());
            return false;
        }
    }

    public function delete(): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "DELETE FROM pack_event WHERE id_pack_event = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->id_pack_event]);
        } catch (PDOException $e) {
            error_log("Erreur delete pack_event: " . $e->getMessage());
            return false;
        }
    }

    public static function deleteByPack(int $id_pack): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "DELETE FROM pack_event WHERE id_pack = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$id_pack]);
        } catch (PDOException $e) {
            error_log("Erreur deleteByPack pack_event: " . $e->getMessage());
            return false;
        }
    }

    public static function deleteByEvent(int $id_event): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "DELETE FROM pack_event WHERE id_event = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$id_event]);
        } catch (PDOException $e) {
            error_log("Erreur deleteByEvent pack_event: " . $e->getMessage());
            return false;
        }
    }

    // Méthodes utilitaires
    public static function linkPackEvent(int $id_pack, int $id_event, string $statut = 'actif'): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "INSERT IGNORE INTO pack_event (id_pack, id_event, statut) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$id_pack, $id_event, $statut]);
        } catch (PDOException $e) {
            error_log("Erreur linkPackEvent: " . $e->getMessage());
            return false;
        }
    }

    public static function unlinkPackEvent(int $id_pack, int $id_event): bool {
        try {
            $pdo = config::getConnexion();
            $sql = "DELETE FROM pack_event WHERE id_pack = ? AND id_event = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$id_pack, $id_event]);
        } catch (PDOException $e) {
            error_log("Erreur unlinkPackEvent: " . $e->getMessage());
            return false;
        }
    }
}
?>
