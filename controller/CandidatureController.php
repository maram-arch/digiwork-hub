<?php
// controller/CandidatureController.php
// Contrôleur Candidature — méthodes CRUD + méthodes avec jointure offre
 
require_once __DIR__ . '/../model/Candidature.php';
require_once __DIR__ . '/../config/config.php';
 
class CandidatureController
{
    // ==============================================================
    // CRUD DE BASE
    // ==============================================================
 
    /** Ajouter une candidature */
    public function addCandidature(Candidature $c): bool
    {
        return $c->addCandidature();
    }
 
    /** Supprimer (utilisateur) */
    public function deleteCandidature(int $id_user, int $id_offer): bool
    {
        $model = new Candidature($id_user, $id_offer);
        return $model->deleteCandidature($id_user, $id_offer);
    }
 
    /** Supprimer (admin) */
    public function deleteCandidatureAdmin(int $id_user, int $id_offer): bool
    {
        $model = new Candidature($id_user, $id_offer);
        return $model->deleteCandidatureAdmin($id_user, $id_offer);
    }
 
    /** Mettre à jour le statut */
    public function updateStatut(int $id_user, int $id_offer, string $statut): bool
    {
        $model = new Candidature($id_user, $id_offer);
        return $model->updateStatut($id_user, $id_offer, $statut);
    }
 
    /** Mettre à jour CV + lettre */
    public function updateCandidature(int $id_user, int $id_offer, string $cv, string $lettre): bool
    {
        $model = new Candidature($id_user, $id_offer);
        return $model->updateCandidature($id_user, $id_offer, $cv, $lettre);
    }
 
    // ==============================================================
    // ★ MÉTHODES AVEC JOINTURE (délèguent au modèle)
    // ==============================================================
 
    /**
     * Toutes les candidatures + titre/adresse de l'offre
     * Utilisé : backoffice listCandidatures.php
     */
    public function getAllCandidatures(): array
    {
        $model = new Candidature(0, 0);
        return $model->getAllCandidaturesWithOffre();
    }
 
    /**
     * Candidatures d'un utilisateur + infos offre
     * Utilisé : frontoffice mes_candidatures.php
     * Retourne un tableau (remplace l'ancien PDOStatement)
     */
    public function getCandidaturesByUser(int $id_user): array
    {
        $model = new Candidature($id_user, 0);
        return $model->getCandidaturesByUserWithOffre($id_user);
    }
 
    /**
     * Une candidature avec infos offre (modifier/supprimer)
     */
    public function getCandidatureById(int $id_user, int $id_offer): ?array
    {
        $model = new Candidature($id_user, $id_offer);
        return $model->getCandidatureByIdWithOffre($id_user, $id_offer);
    }
 
    /**
     * Nombre de candidatures par offre (dashboard admin)
     */
    public function getNbCandidaturesParOffre(): array
    {
        $model = new Candidature(0, 0);
        return $model->getNbCandidaturesParOffre();
    }
}
 