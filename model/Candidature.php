<?php
// model/Candidature.php
// Modèle Candidature avec méthodes de jointure avec la table offre
 
require_once __DIR__ . '/../config/config.php';
 
class Candidature
{
    // ── Propriétés ──────────────────────────────────────────────
    private int    $id_user;
    private int    $id_offer;
    private string $cv;
    private string $Lettre;
    private string $Date;
    private string $Statut;
 
    public function __construct(
        int    $id_user,
        int    $id_offer,
        string $cv     = '',
        string $Lettre = '',
        string $Date   = '',
        string $Statut = 'en attente'
    ) {
        $this->id_user  = $id_user;
        $this->id_offer = $id_offer;
        $this->cv       = $cv;
        $this->Lettre   = $Lettre;
        $this->Date     = $Date ?: date('Y-m-d');
        $this->Statut   = $Statut;
    }
 
    // ── Getters ─────────────────────────────────────────────────
    public function getIdUser()  { return $this->id_user;  }
    public function getIdOffer() { return $this->id_offer; }
    public function getCv()      { return $this->cv;       }
    public function getLettre()  { return $this->Lettre;   }
    public function getDate()    { return $this->Date;     }
    public function getStatut()  { return $this->Statut;   }
 
    // ── Setters ─────────────────────────────────────────────────
    public function setCv(string $cv)         { $this->cv     = $cv;     }
    public function setLettre(string $l)      { $this->Lettre = $l;      }
    public function setStatut(string $s)      { $this->Statut = $s;      }
 
    // ==============================================================
    // CRUD DE BASE
    // ==============================================================
 
    /** Ajouter une candidature */
    public function addCandidature(): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
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
        } catch (PDOException $e) {
            error_log('addCandidature: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Supprimer une candidature (utilisateur) */
    public function deleteCandidature(int $id_user, int $id_offer): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "DELETE FROM condidateur WHERE id_user = :id_user AND id_offer = :id_offer"
            );
            return $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
        } catch (PDOException $e) {
            error_log('deleteCandidature: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Supprimer une candidature (admin) */
    public function deleteCandidatureAdmin(int $id_user, int $id_offer): bool
    {
        return $this->deleteCandidature($id_user, $id_offer);
    }
 
    /** Mettre à jour le statut */
    public function updateStatut(int $id_user, int $id_offer, string $statut): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "UPDATE condidateur SET Statut = :statut
                 WHERE id_user = :id_user AND id_offer = :id_offer"
            );
            return $stmt->execute([
                ':statut'   => $statut,
                ':id_user'  => $id_user,
                ':id_offer' => $id_offer,
            ]);
        } catch (PDOException $e) {
            error_log('updateStatut: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Mettre à jour CV + lettre */
    public function updateCandidature(int $id_user, int $id_offer, string $cv, string $lettre): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "UPDATE condidateur SET cv = :cv, Lettre = :lettre
                 WHERE id_user = :id_user AND id_offer = :id_offer"
            );
            return $stmt->execute([
                ':cv'       => $cv,
                ':lettre'   => $lettre,
                ':id_user'  => $id_user,
                ':id_offer' => $id_offer,
            ]);
        } catch (PDOException $e) {
            error_log('updateCandidature: ' . $e->getMessage());
            return false;
        }
    }
 
    // ==============================================================
    // ★ MÉTHODES AVEC JOINTURE (INNER JOIN offre)
    // ==============================================================
 
    /**
     * Toutes les candidatures + infos de l'offre associée
     * Utilisé : backoffice listCandidatures.php
     */
    public function getAllCandidaturesWithOffre(): array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    c.id_user,
                    c.id_offer,
                    c.cv,
                    c.Lettre,
                    c.Date,
                    c.Statut,
                    o.titre   AS titre_offre,
                    o.adresse AS adresse_offre,
                    o.type    AS type_offre
                 FROM condidateur c
                 INNER JOIN offre o ON c.id_offer = o.id_offer
                 ORDER BY c.Date DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getAllCandidaturesWithOffre: ' . $e->getMessage());
            return [];
        }
    }
 
    /**
     * Candidatures d'un utilisateur + infos de l'offre associée
     * Utilisé : frontoffice mes_candidatures.php
     */
    public function getCandidaturesByUserWithOffre(int $id_user): array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    c.id_user,
                    c.id_offer,
                    c.cv,
                    c.Lettre,
                    c.Date,
                    c.Statut,
                    o.titre      AS titre_offre,
                    o.adresse    AS adresse,
                    o.type       AS type_offre,
                    o.date_limite
                 FROM condidateur c
                 INNER JOIN offre o ON c.id_offer = o.id_offer
                 WHERE c.id_user = :id_user
                 ORDER BY c.Date DESC"
            );
            $stmt->execute([':id_user' => $id_user]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getCandidaturesByUserWithOffre: ' . $e->getMessage());
            return [];
        }
    }
 
    /**
     * Une candidature unique avec infos offre (pour modifier / supprimer)
     */
    public function getCandidatureByIdWithOffre(int $id_user, int $id_offer): ?array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    c.*,
                    o.titre   AS titre_offre,
                    o.adresse AS adresse_offre
                 FROM condidateur c
                 INNER JOIN offre o ON c.id_offer = o.id_offer
                 WHERE c.id_user = :id_user AND c.id_offer = :id_offer"
            );
            $stmt->execute([':id_user' => $id_user, ':id_offer' => $id_offer]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('getCandidatureByIdWithOffre: ' . $e->getMessage());
            return null;
        }
    }
 
    /**
     * Nombre de candidatures par offre (jointure LEFT JOIN)
     * Utile pour le dashboard admin
     */
    public function getNbCandidaturesParOffre(): array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    o.id_offer,
                    o.titre,
                    COUNT(c.id_user) AS nb_candidatures
                 FROM offre o
                 LEFT JOIN condidateur c ON o.id_offer = c.id_offer
                 GROUP BY o.id_offer, o.titre
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