<?php
require_once __DIR__ . '/../config/config.php';

class OffreController {

    public function addOffre($offre) {

        $sql = "INSERT INTO offre 
        (titre, description, competences, date_limite, adresse, type, id_entreprise)
        VALUES 
        (:titre, :description, :competences, :date_limite, :adresse, :type, :id_entreprise)";

        $db = Config::getconnexion(); // ⚠️ bien écrire getconnexion

        try {
            $query = $db->prepare($sql);

            $query->execute([
                'titre' => $offre->getTitre(),
                'description' => $offre->getDescription(),
                'competences' => $offre->getCompetences(),
                'date_limite' => $offre->getDateLimite(),
                'adresse' => $offre->getAdresse(),
                'type' => $offre->getType(),
                'id_entreprise' => $offre->getIdEntreprise(),
            ]);

        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}
?>