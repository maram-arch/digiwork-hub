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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre = new OffreFormData($_POST);
    if ($offre->getTitre() === '' || $offre->getDescription() === '' || $offre->getCompetences() === '' || $offre->getDateLimite() === '' || $offre->getAdresse() === '' || $offre->getType() === '' || $offre->getIdEntreprise() <= 0) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        (new OffreController())->addOffre($offre);
        header('Location: listOffres.php?status=success&msg=' . urlencode('Offre ajoutee avec succes.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une offre - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Ajouter une offre</h4>
                    <a class="btn btn-light" href="listOffres.php">Retour</a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>Titre</label>
                            <input class="form-control" name="titre" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Competences</label>
                            <input class="form-control" name="competences" placeholder="PHP, JavaScript" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Date limite</label>
                                <input class="form-control" type="date" name="date_limite" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="">-- Selectionner --</option>
                                    <option>CDI</option>
                                    <option>CDD</option>
                                    <option>Stage</option>
                                    <option>Freelance</option>
                                    <option>Alternance</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Adresse</label>
                            <input class="form-control" name="adresse" required>
                        </div>
                        <div class="form-group">
                            <label>ID entreprise</label>
                            <input class="form-control" type="number" name="id_entreprise" min="1" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
