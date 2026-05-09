<?php
require_once __DIR__ . '/../config/config.php';

class Candidature
{
    private int $id_user;
    private int $id_offer;
    private string $cv;
    private string $Lettre;
    private string $Date;
    private string $Statut;

    public function __construct(
        int $id_user,
        int $id_offer,
        string $cv = '',
        string $Lettre = '',
        string $Date = '',
        string $Statut = 'en attente'
    ) {
        $this->id_user = $id_user;
        $this->id_offer = $id_offer;
        $this->cv = $cv;
        $this->Lettre = $Lettre;
        $this->Date = $Date ?: date('Y-m-d');
        $this->Statut = $Statut;
    }

    public function getIdUser() { return $this->id_user; }
    public function getIdOffer() { return $this->id_offer; }
    public function getCv() { return $this->cv; }
    public function getLettre() { return $this->Lettre; }
    public function getDate() { return $this->Date; }
    public function getStatut() { return $this->Statut; }

    public function setCv(string $cv) { $this->cv = $cv; }
    public function setLettre(string $l) { $this->Lettre = $l; }
    public function setStatut(string $s) { $this->Statut = $s; }

    private function useModernSchema(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM condidateur LIKE 'id_offer'");
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return true;
        }
    }

    private function useModernOffreSchema(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'offre'");
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return true;
        }
    }

    private function candidatureSelect(PDO $pdo): string
    {
        if ($this->useModernSchema($pdo)) {
            return 'c.id_user, c.id_offer, c.cv, c.Lettre, c.Date, c.Statut';
        }

        return 'c.id_user, c.`id-offer` AS id_offer, c.cv, c.`lettre-de-motivation` AS Lettre, c.`date-envoia` AS Date, c.staut AS Statut';
    }

    private function offreJoin(PDO $pdo): array
    {
        if ($this->useModernOffreSchema($pdo)) {
            return [
                'table' => 'offre o',
                'on' => $this->useModernSchema($pdo) ? 'c.id_offer = o.id_offer' : 'c.`id-offer` = o.id_offer',
                'fields' => 'o.titre AS titre_offre, o.adresse AS adresse, o.adresse AS adresse_offre, o.type AS type_offre, o.date_limite',
            ];
        }

        return [
            'table' => 'offer o',
            'on' => $this->useModernSchema($pdo) ? 'c.id_offer = o.`id-offer`' : 'c.`id-offer` = o.`id-offer`',
            'fields' => 'o.titre AS titre_offre, o.adresse AS adresse, o.adresse AS adresse_offre, o.type AS type_offre, o.`date-limiter` AS date_limite',
        ];
    }

    public function addCandidature(): bool
    {
        try {
            $pdo = Config::getConnexion();
            $modern = $this->useModernSchema($pdo);
            $sql = $modern
                ? "INSERT INTO condidateur (id_user, id_offer, cv, Lettre, Date, Statut)
                   VALUES (:id_user, :id_offer, :cv, :lettre, :date, :statut)"
                : "INSERT INTO condidateur (id_user, `id-offer`, cv, `lettre-de-motivation`, `date-envoia`, staut)
                   VALUES (:id_user, :id_offer, :cv, :lettre, :date, :statut)";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':id_user' => $this->id_user,
                ':id_offer' => $this->id_offer,
                ':cv' => $this->cv,
                ':lettre' => $this->Lettre,
                ':date' => $this->Date,
                ':statut' => $this->Statut,
            ]);
        } catch (PDOException $e) {
            error_log('addCandidature: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCandidature(int $id_user, int $id_offer): bool
    {
        try {
            $pdo = Config::getConnexion();
            $offerColumn = $this->useModernSchema($pdo) ? 'id_offer' : '`id-offer`';
            $stmt = $pdo->prepare("DELETE FROM condidateur WHERE id_user = :id_user AND $offerColumn = :id_offer");
            return $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
        } catch (PDOException $e) {
            error_log('deleteCandidature: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCandidatureAdmin(int $id_user, int $id_offer): bool
    {
        return $this->deleteCandidature($id_user, $id_offer);
    }

    public function updateStatut(int $id_user, int $id_offer, string $statut): bool
    {
        try {
            $pdo = Config::getConnexion();
            $modern = $this->useModernSchema($pdo);
            $statusColumn = $modern ? 'Statut' : 'staut';
            $offerColumn = $modern ? 'id_offer' : '`id-offer`';
            $stmt = $pdo->prepare("UPDATE condidateur SET $statusColumn = :statut WHERE id_user = :id_user AND $offerColumn = :id_offer");
            return $stmt->execute([':statut' => $statut, ':id_user' => $id_user, ':id_offer' => $id_offer]);
        } catch (PDOException $e) {
            error_log('updateStatut: ' . $e->getMessage());
            return false;
        }
    }

    public function updateCandidature(int $id_user, int $id_offer, string $cv, string $lettre): bool
    {
        try {
            $pdo = Config::getConnexion();
            $modern = $this->useModernSchema($pdo);
            $letterColumn = $modern ? 'Lettre' : '`lettre-de-motivation`';
            $offerColumn = $modern ? 'id_offer' : '`id-offer`';
            $stmt = $pdo->prepare("UPDATE condidateur SET cv = :cv, $letterColumn = :lettre WHERE id_user = :id_user AND $offerColumn = :id_offer");
            return $stmt->execute([':cv' => $cv, ':lettre' => $lettre, ':id_user' => $id_user, ':id_offer' => $id_offer]);
        } catch (PDOException $e) {
            error_log('updateCandidature: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllCandidaturesWithOffre(): array
    {
        try {
            $pdo = Config::getConnexion();
            $join = $this->offreJoin($pdo);
            $stmt = $pdo->prepare(
                "SELECT {$this->candidatureSelect($pdo)}, {$join['fields']}
                 FROM condidateur c
                 INNER JOIN {$join['table']} ON {$join['on']}
                 ORDER BY Date DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getAllCandidaturesWithOffre: ' . $e->getMessage());
            return [];
        }
    }

    public function getCandidaturesByUserWithOffre(int $id_user): array
    {
        try {
            $pdo = Config::getConnexion();
            $join = $this->offreJoin($pdo);
            $stmt = $pdo->prepare(
                "SELECT {$this->candidatureSelect($pdo)}, {$join['fields']}
                 FROM condidateur c
                 INNER JOIN {$join['table']} ON {$join['on']}
                 WHERE c.id_user = :id_user
                 ORDER BY Date DESC"
            );
            $stmt->execute([':id_user' => $id_user]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getCandidaturesByUserWithOffre: ' . $e->getMessage());
            return [];
        }
    }

    public function getCandidatureByIdWithOffre(int $id_user, int $id_offer): ?array
    {
        try {
            $pdo = Config::getConnexion();
            $join = $this->offreJoin($pdo);
            $offerColumn = $this->useModernSchema($pdo) ? 'c.id_offer' : 'c.`id-offer`';
            $stmt = $pdo->prepare(
                "SELECT {$this->candidatureSelect($pdo)}, {$join['fields']}
                 FROM condidateur c
                 INNER JOIN {$join['table']} ON {$join['on']}
                 WHERE c.id_user = :id_user AND $offerColumn = :id_offer"
            );
            $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('getCandidatureByIdWithOffre: ' . $e->getMessage());
            return null;
        }
    }

    public function getNbCandidaturesParOffre(): array
    {
        try {
            $pdo = Config::getConnexion();
            $modernOffre = $this->useModernOffreSchema($pdo);
            $modernCand = $this->useModernSchema($pdo);
            $offreTable = $modernOffre ? 'offre' : 'offer';
            $offreId = $modernOffre ? 'o.id_offer' : 'o.`id-offer`';
            $candOffer = $modernCand ? 'c.id_offer' : 'c.`id-offer`';

            $stmt = $pdo->prepare(
                "SELECT $offreId AS id_offer, o.titre, COUNT(c.id_user) AS nb_candidatures
                 FROM $offreTable o
                 LEFT JOIN condidateur c ON $offreId = $candOffer
                 GROUP BY $offreId, o.titre
                 ORDER BY nb_candidatures DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getNbCandidaturesParOffre: ' . $e->getMessage());
            return [];
        }
    }
}
