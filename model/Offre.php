<?php
class Offre {
    private $titre, $description, $competences, $date_limite, $adresse, $type, $id_entreprise;

    public function __construct($titre, $description, $competences, $date_limite, $adresse, $type, $id_entreprise) {
        $this->titre = $titre;
        $this->description = $description;
        $this->competences = $competences;
        $this->date_limite = $date_limite;
        $this->adresse = $adresse;
        $this->type = $type;
        $this->id_entreprise = $id_entreprise;
    }

    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getCompetences() { return $this->competences; }
    public function getDateLimite() { return $this->date_limite; }
    public function getAdresse() { return $this->adresse; }
    public function getType() { return $this->type; }
    public function getIdEntreprise() { return $this->id_entreprise; }
}
?>