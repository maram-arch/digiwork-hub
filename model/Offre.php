<?php
class Offre
{
    private ?int    $id_offre      = null;
    private ?string $titre         = null;
    private ?string $description   = null;
    private ?string $competences   = null;
    private ?string $date_limite   = null;
    private ?string $adresse       = null;
    private ?string $type          = null;
    private ?int    $id_entreprise = null;
 
    public function __construct(
        $id       = null,
        $t        = null,
        $d        = null,
        $c        = null,
        $date     = null,
        $a        = null,
        $type     = null,
        $id_ent   = null
    ) {
        $this->id_offre      = $id;
        $this->titre         = $t;
        $this->description   = $d;
        $this->competences   = $c;
        $this->date_limite   = $date;
        $this->adresse       = $a;
        $this->type          = $type;
        $this->id_entreprise = $id_ent;
    }
 
    // ── GETTERS ──────────────────────────────────────────────
    public function getIdOffre()      { return $this->id_offre; }
    public function getTitre()        { return $this->titre; }
    public function getDescription()  { return $this->description; }
    public function getCompetences()  { return $this->competences; }
    public function getDateLimite()   { return $this->date_limite; }
    public function getAdresse()      { return $this->adresse; }
    public function getType()         { return $this->type; }
    public function getIdEntreprise() { return $this->id_entreprise; }
 
    // ── SETTERS ──────────────────────────────────────────────
    public function setIdOffre($id)          { $this->id_offre      = $id; }
    public function setTitre($t)             { $this->titre         = $t; }
    public function setDescription($d)       { $this->description   = $d; }
    public function setCompetences($c)       { $this->competences   = $c; }
    public function setDateLimite($date)     { $this->date_limite   = $date; }
    public function setAdresse($a)           { $this->adresse       = $a; }
    public function setType($type)           { $this->type          = $type; }
    public function setIdEntreprise($id_ent) { $this->id_entreprise = $id_ent; }
}
?>