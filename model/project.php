<?php

class Projet
{
    private ?int $idProjet;
    private string $titre;
    private string $discription;
    private float $budget;
    private string $statut;
    private int $idUser;
    private int $idOffre;

    public function __construct(
        ?int $idProjet,
        string $titre,
        string $discription,
        float $budget,
        string $statut,
        int $idUser,
        int $idOffre
    ) {
        $this->idProjet = $idProjet;
        $this->titre = $titre;
        $this->discription = $discription;
        $this->budget = $budget;
        $this->statut = $statut;
        $this->idUser = $idUser;
        $this->idOffre = $idOffre;
    }

    public function getIdProjet(): ?int
    {
        return $this->idProjet;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDiscription(): string
    {
        return $this->discription;
    }

    public function getBudget(): float
    {
        return $this->budget;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getIdUser(): int
    {
        return $this->idUser;
    }

    public function getIdOffre(): int
    {
        return $this->idOffre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function setDiscription(string $discription): void
    {
        $this->discription = $discription;
    }

    public function setBudget(float $budget): void
    {
        $this->budget = $budget;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function setIdUser(int $idUser): void
    {
        $this->idUser = $idUser;
    }

    public function setIdOffre(int $idOffre): void
    {
        $this->idOffre = $idOffre;
    }
}