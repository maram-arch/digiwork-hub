<?php
class Commentaire {
    private $id_commentaire;
    private $contenu;
    private $id_publication;
    private $date_creation;

    public function __construct($contenu, $id_publication) {
        $this->contenu        = $contenu;
        $this->id_publication = $id_publication;
    }

    public function getId()            { return $this->id_commentaire; }
    public function getContenu()       { return $this->contenu; }
    public function getIdPublication() { return $this->id_publication; }
    public function getDate()          { return $this->date_creation; }
    public function setContenu($c)     { $this->contenu = $c; }
}