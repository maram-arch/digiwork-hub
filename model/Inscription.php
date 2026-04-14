<?php
class Inscription {
    private ?int $id_inscription;
    private ?int $id_user;
    private ?int $id_event;
    private ?string $date_inscription;
    private ?string $statut;

    public function __construct(?int $id_inscription = null, ?int $id_user = null, ?int $id_event = null, ?string $date_inscription = null, ?string $statut = null) {
        $this->id_inscription = $id_inscription;
        $this->id_user = $id_user;
        $this->id_event = $id_event;
        $this->date_inscription = $date_inscription;
        $this->statut = $statut;
    }

    public function getIdInscription(): ?int { return $this->id_inscription; }
    public function getIdUser(): ?int { return $this->id_user; }
    public function getIdEvent(): ?int { return $this->id_event; }
    public function getDateInscription(): ?string { return $this->date_inscription; }
    public function getStatut(): ?string { return $this->statut; }

    public function setIdInscription(?int $id_inscription): void { $this->id_inscription = $id_inscription; }
    public function setIdUser(?int $id_user): void { $this->id_user = $id_user; }
    public function setIdEvent(?int $id_event): void { $this->id_event = $id_event; }
    public function setDateInscription(?string $date_inscription): void { $this->date_inscription = $date_inscription; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
}
