<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Candidature.php';

class CandidatureController {

    private PDO $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    /* ══ Ajouter une candidature ══ */
    public function addCandidature(Candidature $c): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO condidateur (id_user, id_offer, cv, Lettre, Date, Statut)
             VALUES (:id_user, :id_offer, :cv, :lettre, :date, :statut)"
        );
        return $stmt->execute([
            ':id_user'  => $c->getIdUser(),
            ':id_offer' => $c->getIdOffer(),
            ':cv'       => $c->getCv(),
            ':lettre'   => $c->getLettre(),
            ':date'     => $c->getDate(),
            ':statut'   => $c->getStatut(),
        ]);
    }

    /* ══ Candidatures d'un utilisateur (frontoffice) ══ */
    public function getCandidaturesByUser(int $id_user): PDOStatement {
        $stmt = $this->db->prepare(
            "SELECT c.id_user, c.id_offer, c.cv, c.Lettre, c.Date, c.Statut,
                    o.titre AS titre_offre, o.type AS type_offre, o.adresse
             FROM condidateur c
             JOIN offre o ON c.id_offer = o.id_offer
             WHERE c.id_user = :id_user
             ORDER BY c.Date DESC"
        );
        $stmt->execute([':id_user' => $id_user]);
        return $stmt;
    }

    /* ══ Toutes les candidatures (backoffice) ══ */
    public function getAllCandidatures(): array {
        $stmt = $this->db->query(
            "SELECT c.id_user, c.id_offer, c.cv, c.Lettre, c.Date, c.Statut,
                    o.titre AS titre_offre
             FROM condidateur c
             LEFT JOIN offre o ON o.id_offer = c.id_offer
             ORDER BY c.Date DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ══ Une candidature par clé composite ══ */
    public function getCandidatureById(int $id_user, int $id_offer): array|false {
        $stmt = $this->db->prepare(
            "SELECT c.id_user, c.id_offer, c.cv, c.Lettre, c.Date, c.Statut,
                    o.titre AS titre_offre
             FROM condidateur c
             JOIN offre o ON c.id_offer = o.id_offer
             WHERE c.id_user = :id_user AND c.id_offer = :id_offer"
        );
        $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ══ Modifier CV + Lettre (frontoffice, accepte les deux formats de statut) ══ */
    public function updateCandidature(int $id_user, int $id_offer, string $cv, string $lettre): bool {
        $stmt = $this->db->prepare(
            "UPDATE condidateur
             SET cv = :cv, Lettre = :lettre
             WHERE id_user = :id_user AND id_offer = :id_offer
             AND (Statut = 'en attente' OR Statut = 'en_attente')"
        );
        $stmt->execute([
            ':cv'       => $cv,
            ':lettre'   => $lettre,
            ':id_user'  => $id_user,
            ':id_offer' => $id_offer,
        ]);
        return $stmt->rowCount() > 0;
    }

    /* ══ Supprimer (frontoffice, seulement si en attente) ══ */
    public function deleteCandidature(int $id_user, int $id_offer): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM condidateur
             WHERE id_user = :id_user AND id_offer = :id_offer
             AND (Statut = 'en attente' OR Statut = 'en_attente')"
        );
        $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
        return $stmt->rowCount() > 0;
    }

    /* ══ Supprimer par admin (backoffice, tous statuts) ══ */
    public function deleteCandidatureAdmin(int $id_user, int $id_offer): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM condidateur
             WHERE id_user = :id_user AND id_offer = :id_offer"
        );
        $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
        return $stmt->rowCount() > 0;
    }

    /* ══ Changer Statut (backoffice admin) ══ */
    public function updateStatut(int $id_user, int $id_offer, string $statut): bool {
        $allowed = ['accepte', 'refuse', 'en_attente'];
        if (!in_array($statut, $allowed)) return false;
        $stmt = $this->db->prepare(
            "UPDATE condidateur SET Statut = :statut
             WHERE id_user = :id_user AND id_offer = :id_offer"
        );
        return $stmt->execute([
            ':statut'   => $statut,
            ':id_user'  => $id_user,
            ':id_offer' => $id_offer,
        ]);
    }
}