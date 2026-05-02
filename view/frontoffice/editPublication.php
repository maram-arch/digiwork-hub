<<<<<<< HEAD
<style id="theme-style">
    body { background: #fff; color: #000; transition: 0.3s; }
    body.dark-mode { background: #1e1e2f; color: #eee; }
    body.dark-mode .card, body.dark-mode .pub-card, body.dark-mode .form-container { background: #2a2a3a; color: #eee; border-color: #444; }
</style>
<button id="theme-toggle" class="btn btn-sm btn-secondary position-fixed bottom-0 end-0 m-3">🌓 Thème</button>
<script>
document.getElementById('theme-toggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
</script>
<?php
// Modification d'une publication (frontoffice)
require_once __DIR__ . '/../../controller/PublicationController.php';
require_once __DIR__ . '/../../model/Publication.php';
require_once __DIR__ . '/../../config/Config.php';
session_start();

$id_publication = (int)($_GET['id'] ?? 0);
if (!$id_publication) {
    header('Location: publications.php?status=error&msg=ID+manquant');
    exit;
}

$pub = Publication::getByIdWithUser($id_publication);
if (!$pub) {
    header('Location: publications.php?status=error&msg=Publication+introuvable');
    exit;
}

// Vérifier que l'utilisateur connecté est l'auteur
if (($_SESSION['id_user'] ?? 0) != $pub['id_user']) {
    header('Location: publications.php?status=error&msg=Non+autorisé');
    exit;
}

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $categorie = $_POST['categorie'] ?? 'general';
    $is_event = isset($_POST['is_event']) ? 1 : 0;
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
    $event_lieu = !empty($_POST['event_lieu']) ? trim($_POST['event_lieu']) : null;

    $errors = [];
    if (strlen($titre) < 3) $errors[] = "Titre trop court (min 3).";
    if (strlen($contenu) < 10) $errors[] = "Contenu trop court (min 10).";

    // Gestion de l'image (optionnelle)
    $image = $pub['image']; // garde l'ancienne par défaut
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../../public/uploads/publications/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $fileName = uniqid('pub_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                // Supprimer l'ancienne image si elle existe
                if ($image && file_exists(__DIR__ . '/../../' . $image)) {
                    unlink(__DIR__ . '/../../' . $image);
                }
                $image = 'uploads/publications/' . $fileName;
            } else {
                $errors[] = "Erreur upload image.";
            }
        } else {
            $errors[] = "Format image non autorisé.";
        }
    }

    if (empty($errors)) {
        $titre = htmlspecialchars($titre);
        $contenu = htmlspecialchars($contenu);
        $event_lieu = htmlspecialchars($event_lieu);
        $update = new Publication(
            $id_publication, $titre, $contenu, null, null, $categorie, $image, null, 0, 0, $is_event, $event_date, $event_lieu
        );
        $ctrl = new PublicationController();
        $ctrl->updatePublication($id_publication, $titre, $contenu, $categorie, $image, $is_event, $event_date, $event_lieu);
        header("Location: detail_publication.php?id=$id_publication&status=success&msg=" . urlencode("Publication modifiée"));
        exit;
    } else {
        $errorMessage = implode("<br>", $errors);
    }
}
?>
=======
>>>>>>> 6b3b218dc29227adaacddd025b9d802292528038
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <title>Modifier la publication</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
</head>

<body>
<header class="header header-6">
    <div class="navbar-area">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.php"><img src="assets/img/logo/logo.png" style="width:250px;"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="toggler-icon"></span><span class="toggler-icon"></span><span class="toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link active" href="publications.php">Forum</a></li>
                        <li class="nav-item"><a class="nav-link" href="mes_commentaires.php">Mes commentaires</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>
<div class="container py-5">
    <div class="card" style="max-width:800px; margin:0 auto;">
        <div class="card-body">
            <h2 class="mb-4">✏️ Modifier la publication</h2>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= $errorMessage ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="mb-3">
                    <label class="form-label">Titre *</label>
                    <input type="text" name="titre" id="titre" class="form-control" value="<?= htmlspecialchars($pub['titre']) ?>">
                    <div id="titreError" class="text-danger small"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contenu *</label>
                    <textarea name="contenu" id="contenu" class="form-control" rows="6"><?= htmlspecialchars($pub['contenu']) ?></textarea>
                    <div id="contenuError" class="text-danger small"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <select name="categorie" class="form-select">
                        <option value="general" <?= $pub['categorie']=='general'?'selected':'' ?>>Général</option>
                        <option value="stage" <?= $pub['categorie']=='stage'?'selected':'' ?>>Stage</option>
                        <option value="job" <?= $pub['categorie']=='job'?'selected':'' ?>>Job</option>
                        <option value="question" <?= $pub['categorie']=='question'?'selected':'' ?>>Question</option>
                        <option value="evenement" <?= $pub['categorie']=='evenement'?'selected':'' ?>>Événement</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image actuelle</label><br>
                    <?php if ($pub['image']): ?>
                        <img src="../../<?= htmlspecialchars($pub['image']) ?>" style="max-height:100px;" class="mb-2">
                    <?php else: ?>
                        <span class="text-muted">Aucune image</span>
                    <?php endif; ?>
                    <input type="file" name="image" id="image" class="form-control mt-2" accept="image/jpeg,image/png,image/gif">
                    <small class="text-muted">Laissez vide pour conserver l'image actuelle (JPG, PNG, GIF max 2 Mo).</small>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_event" id="is_event" value="1" <?= $pub['is_event'] ? 'checked' : '' ?>>
                    <label class="form-check-label">C'est un événement</label>
                </div>
                <div id="eventFields" style="display: <?= $pub['is_event'] ? 'block' : 'none' ?>;">
                    <div class="mb-3">
                        <label class="form-label">Date événement</label>
                        <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($pub['event_date'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lieu</label>
                        <input type="text" name="event_lieu" class="form-control" value="<?= htmlspecialchars($pub['event_lieu'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="detail_publication.php?id=<?= $id_publication ?>" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('is_event').addEventListener('change', function() {
        document.getElementById('eventFields').style.display = this.checked ? 'block' : 'none';
    });
    function validateForm() {
        let ok = true;
        let titre = document.getElementById('titre').value.trim();
        let contenu = document.getElementById('contenu').value.trim();
        document.getElementById('titreError').innerHTML = '';
        document.getElementById('contenuError').innerHTML = '';
        if (titre.length < 3) {
            document.getElementById('titreError').innerHTML = 'Titre trop court (min 3)';
            ok = false;
        }
        if (contenu.length < 10) {
            document.getElementById('contenuError').innerHTML = 'Contenu trop court (min 10)';
            ok = false;
        }
        return ok;
    }
</script>
=======
    <title>Modifier la Publication</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/LineIcons.2.0.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        body { background: #F3F3F3; font-family: 'Heebo', sans-serif; }
        .page-header { background: #fff; padding: 18px 28px; border-bottom: 1px solid #e8e8e8; margin-bottom: 28px; }
        .page-header h1 { font-family: 'Fira Sans', sans-serif; font-size: 22px; color: #323450; }
        .back-link { color: #2F80ED; font-size: 20px; text-decoration: none; margin-right: 8px; }
        .back-link:hover { text-decoration: none; }
        .wrapper { padding: 0 28px 40px; }
        .form-card { background: #fff; border-radius: 10px; border-top: 4px solid #2F80ED; max-width: 600px; padding: 35px 40px; box-shadow: 0px 5px 25px rgba(218,211,211,0.3); }
        .pub-id-badge { background: rgba(47,128,237,0.1); color: #2F80ED; font-size: 12px; padding: 3px 12px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .form-card label { display: block; font-weight: 600; color: #323450; font-size: 14px; margin-bottom: 7px; }
        .form-card input[type=text] { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; font-family: 'Heebo', sans-serif; color: #585978; box-shadow: 0px 2px 8px rgba(218,211,211,0.2); }
        .form-card input[type=text]:focus { border-color: #2F80ED; outline: none; }
        .form-card textarea { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; font-family: 'Heebo', sans-serif; color: #585978; box-shadow: 0px 2px 8px rgba(218,211,211,0.2); resize: vertical; }
        .form-card textarea:focus { border-color: #2F80ED; outline: none; }
        .btn-submit { background: #2F80ED; color: #fff; border: none; padding: 11px 28px; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; font-family: 'Heebo', sans-serif; }
        .btn-submit:hover { background: #1a6fd4; }
        .btn-cancel { color: #585978; font-size: 14px; text-decoration: none; margin-left: 16px; }
        .btn-cancel:hover { color: #2F80ED; text-decoration: none; }
        .error-msg { background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.3); color: #dc3545; padding: 10px 16px; border-radius: 5px; margin-bottom: 18px; font-size: 13px; }
    </style>
</head>
<body>

<div class="page-header">
    <h1>
        <a href="index.php?action=list" class="back-link">&#8592;</a>
        Modifier la Publication
    </h1>
</div>

<div class="wrapper">
    <div class="form-card">

        <div class="pub-id-badge">Publication #<?php echo $publication['id_publication']; ?></div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php?action=edit&id=<?php echo $publication['id_publication']; ?>" method="POST">

            <label>Titre :</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($publication['titre']); ?>">
            <br><br>

            <label>Contenu :</label>
            <textarea name="contenu" rows="6"><?php echo htmlspecialchars($publication['contenu']); ?></textarea>
            <br><br>

            <button type="submit" class="btn-submit">Enregistrer</button>
            <a href="index.php?action=list" class="btn-cancel">Annuler</a>

        </form>
    </div>
</div>

>>>>>>> 6b3b218dc29227adaacddd025b9d802292528038
</body>
</html>