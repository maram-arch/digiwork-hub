<?php
require_once __DIR__ . '/../config/config.php';

class Candidature {

    private int    $id_user;
    private int    $id_offer;
    private string $cv;
    private string $Lettre;
    private string $Date;
    private string $Statut;

    public function __construct(
        int    $id_user  = 0,
        int    $id_offer = 0,
        string $cv       = '',
        string $Lettre   = '',
        string $Date     = '',
        string $Statut   = 'en attente'
    ) {
        $this->id_user  = $id_user;
        $this->id_offer = $id_offer;
        $this->cv       = $cv;
        $this->Lettre   = $Lettre;
        $this->Date     = $Date;
        $this->Statut   = $Statut;
    }

    /* ══ Getters ══ */
    public function getIdUser(): int    { return $this->id_user; }
    public function getIdOffer(): int   { return $this->id_offer; }
    public function getCv(): string     { return $this->cv; }
    public function getLettre(): string { return $this->Lettre; }
    public function getDate(): string   { return $this->Date; }
    public function getStatut(): string { return $this->Statut; }

    /* ══ Setters ══ */
    public function setIdUser(int $v): void    { $this->id_user  = $v; }
    public function setIdOffer(int $v): void   { $this->id_offer = $v; }
    public function setCv(string $v): void     { $this->cv       = $v; }
    public function setLettre(string $v): void { $this->Lettre   = $v; }
    public function setDate(string $v): void   { $this->Date     = $v; }
    public function setStatut(string $v): void { $this->Statut   = $v; }

    private static function getDB(): PDO {
        return Config::getConnexion();
    }

    /* ══ Toutes les candidatures (backoffice) ══ */
    public static function getAllCandidatures(): array {
        $stmt = self::getDB()->query("
            SELECT
                c.id_user,
                c.id_offer,
                c.cv,
                c.Lettre,
                c.Date,
                c.Statut,
                u.nom   AS nom_user,
                o.titre AS titre_offre
            FROM condidateur c
            LEFT JOIN user  u ON u.id       = c.id_user
            LEFT JOIN offre o ON o.id_offer = c.id_offer
            ORDER BY c.Date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ══ Mettre à jour le statut ══ */
    public static function updateStatut(int $id_user, int $id_offer, string $statut): bool {
        $allowed = ['accepte', 'refuse', 'en_attente'];
        if (!in_array($statut, $allowed)) return false;
        $stmt = self::getDB()->prepare(
            "UPDATE condidateur SET Statut = :statut
             WHERE id_user = :id_user AND id_offer = :id_offer"
        );
        return $stmt->execute([
            ':statut'   => $statut,
            ':id_user'  => $id_user,
            ':id_offer' => $id_offer,
        ]);
    }

    /* ══ Supprimer une candidature ══ */
    public static function deleteCandidature(int $id_user, int $id_offer): bool {
        $stmt = self::getDB()->prepare(
            "DELETE FROM condidateur
             WHERE id_user = :id_user AND id_offer = :id_offer"
        );
        return $stmt->execute([
            ':id_user'  => $id_user,
            ':id_offer' => $id_offer,
        ]);
    }

    /* ══ Insérer une candidature (frontoffice) ══ */
    public function save(): bool {
        $stmt = self::getDB()->prepare(
            "INSERT INTO condidateur (id_user, id_offer, cv, Lettre, Date, Statut)
             VALUES (:id_user, :id_offer, :cv, :lettre, :date, :statut)"
        );
        return $stmt->execute([
            ':id_user'  => $this->id_user,
            ':id_offer' => $this->id_offer,
            ':cv'       => $this->cv,
            ':lettre'   => $this->Lettre,
            ':date'     => $this->Date,
            ':statut'   => $this->Statut,
        ]);
    }
}