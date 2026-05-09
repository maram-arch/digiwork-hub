<?php
require_once __DIR__ . '/../../controller/OffreController.php';

class OffreFormData
{
    public function __construct(private array $data) {}
    public function getTitre() { return trim((string)($this->data['titre'] ?? '')); }
    public function getDescription() { return trim((string)($this->data['description'] ?? '')); }
    public function getCompetences() { return trim((string)($this->data['competences'] ?? '')); }
    public function getDateLimite() { return trim((string)($this->data['date_limite'] ?? '')); }
    public function getAdresse() { return trim((string)($this->data['adresse'] ?? '')); }
    public function getType() { return trim((string)($this->data['type'] ?? '')); }
    public function getIdEntreprise() { return (int)($this->data['id_entreprise'] ?? 0); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_offer'] ?? 0);
    if ($id > 0) {
        (new OffreController())->updateOffre(new OffreFormData($_POST), $id);
        header('Location: listOffres.php?status=success&msg=' . urlencode('Offre modifiee avec succes.'));
        exit;
    }
}

header('Location: listOffres.php?status=error&msg=' . urlencode('Modification impossible.'));
exit;
