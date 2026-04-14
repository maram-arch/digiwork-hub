<?php
class Event {
    private ?int $id_event;
    private ?string $titre;
    private ?string $description;
    private ?string $date_event;
    private ?string $heure_event;
    private ?string $lieu;
    private ?int $capacite;
    private ?int $id_organisateur;
    private ?string $date_creation;

    public function __construct(?int $id_event = null, ?string $titre = null, ?string $description = null, ?string $date_event = null, ?string $heure_event = null, ?string $lieu = null, ?int $capacite = null, ?int $id_organisateur = null, ?string $date_creation = null) {
        $this->id_event = $id_event;
        $this->titre = $titre;
        $this->description = $description;
        $this->date_event = $date_event;
        $this->heure_event = $heure_event;
        $this->lieu = $lieu;
        $this->capacite = $capacite;
        $this->id_organisateur = $id_organisateur;
        $this->date_creation = $date_creation;
    }

    // Getters
    public function getIdEvent(): ?int { return $this->id_event; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateEvent(): ?string { return $this->date_event; }
    public function getHeureEvent(): ?string { return $this->heure_event; }
    public function getLieu(): ?string { return $this->lieu; }
    public function getCapacite(): ?int { return $this->capacite; }
    public function getIdOrganisateur(): ?int { return $this->id_organisateur; }
    public function getDateCreation(): ?string { return $this->date_creation; }

    // Setters
    public function setIdEvent(?int $id_event): void { $this->id_event = $id_event; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setDateEvent(?string $date_event): void { $this->date_event = $date_event; }
    public function setHeureEvent(?string $heure_event): void { $this->heure_event = $heure_event; }
    public function setLieu(?string $lieu): void { $this->lieu = $lieu; }
    public function setCapacite(?int $capacite): void { $this->capacite = $capacite; }
    public function setIdOrganisateur(?int $id_organisateur): void { $this->id_organisateur = $id_organisateur; }
    public function setDateCreation(?string $date_creation): void { $this->date_creation = $date_creation; }
}
