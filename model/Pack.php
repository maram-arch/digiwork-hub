<?php
require_once __DIR__ . '/../config/config.php';

class Pack {
    private ?int $id_pack;
    private ?string $nom_pack;
    private ?float $prix;
    private ?string $duree;
    private ?string $description;
    private ?int $nb_proj_max;
    private ?string $support_prioritaire;

    public function __construct(?int $id_pack = null, ?string $nom_pack = null, ?float $prix = null, ?string $duree = null, ?string $description = null, ?int $nb_proj_max = null, ?string $support_prioritaire = null) {
        $this->id_pack = $id_pack;
        $this->nom_pack = $nom_pack;
        $this->prix = $prix;
        $this->duree = $duree;
        $this->description = $description;
        $this->nb_proj_max = $nb_proj_max;
        $this->support_prioritaire = $support_prioritaire;
    }

    // Getters
    public function getIdPack(): ?int { return $this->id_pack; }
    public function getNomPack(): ?string { return $this->nom_pack; }
    public function getPrix(): ?float { return $this->prix; }
    public function getDuree(): ?string { return $this->duree; }
    public function getDescription(): ?string { return $this->description; }
    public function getNbProjMax(): ?int { return $this->nb_proj_max; }
    public function getSupportPrioritaire(): ?string { return $this->support_prioritaire; }

    // Setters
    public function setIdPack(?int $id_pack): void { $this->id_pack = $id_pack; }
    public function setNomPack(?string $nom_pack): void { $this->nom_pack = $nom_pack; }
    public function setPrix(?float $prix): void { $this->prix = $prix; }
    public function setDuree(?string $duree): void { $this->duree = $duree; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setNbProjMax(?int $nb_proj_max): void { $this->nb_proj_max = $nb_proj_max; }
    public function setSupportPrioritaire(?string $support_prioritaire): void { $this->support_prioritaire = $support_prioritaire; }

    // Database methods
    public function getAll() {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT * FROM pack ORDER BY prix ASC');
            return $query;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM pack WHERE `id-pack` = ?');
            $query->execute([$id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getByName($name) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM pack WHERE `nom-pack` = ?');
            $query->execute([$name]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function create() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO pack (`nom-pack`, prix, duree, description, `nb-proj-max`, `support-prioritaire`)
                VALUES (:nom_pack, :prix, :duree, :description, :nb_proj_max, :support_prioritaire)'
            );
            $query->execute([
                'nom_pack' => $this->nom_pack,
                'prix' => $this->prix,
                'duree' => $this->duree,
                'description' => $this->description,
                'nb_proj_max' => $this->nb_proj_max,
                'support_prioritaire' => $this->support_prioritaire
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function update() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE pack SET `nom-pack` = :nom_pack, prix = :prix, duree = :duree,
                description = :description, `nb-proj-max` = :nb_proj_max, `support-prioritaire` = :support_prioritaire
                WHERE `id-pack` = :id_pack'
            );
            $query->execute([
                'nom_pack' => $this->nom_pack,
                'prix' => $this->prix,
                'duree' => $this->duree,
                'description' => $this->description,
                'nb_proj_max' => $this->nb_proj_max,
                'support_prioritaire' => $this->support_prioritaire,
                'id_pack' => $this->id_pack
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function delete() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM pack WHERE `id-pack` = ?');
            $query->execute([$this->id_pack]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}