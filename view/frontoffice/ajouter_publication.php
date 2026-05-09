<?php
// view/frontoffice/ajouter_publication.php

require_once __DIR__ . '/../../config/Config.php';

session_start();
$id_user = $_SESSION['id_user'] ?? 1;
$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];
    $titre     = trim($_POST["titre"] ?? '');
    $contenu   = trim($_POST["contenu"] ?? '');
    $categorie = trim($_POST["categorie"] ?? 'general');
    $is_event  = isset($_POST["is_event"]) ? 1 : 0;
    $event_date = !empty($_POST["event_date"]) ? $_POST["event_date"] : null;
    $event_lieu = !empty($_POST["event_lieu"]) ? trim($_POST["event_lieu"]) : null;
    $date_programmee = !empty($_POST["date_programmee"]) ? $_POST["date_programmee"] : null;

    if (strlen($titre) < 3)      $errors[] = "Titre trop court (min 3).";
    if (strlen($contenu) < 10)   $errors[] = "Contenu trop court (min 10).";
    if (!$categorie)             $errors[] = "Catégorie obligatoire.";
    if ($date_programmee && strtotime($date_programmee) <= time()) {
        $errors[] = "Date programmée dans le futur.";
    }

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../../public/uploads/publications/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Format image non autorisé (JPG, PNG, GIF).";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image trop volumineuse (max 2 Mo).";
        } else {
            $fileName = uniqid('pub_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $image = 'uploads/publications/' . $fileName;
            } else {
                $errors[] = "Erreur upload image.";
            }
        }
    }

    if (empty($errors)) {
        $titre   = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        $contenu = htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');
        $event_lieu = htmlspecialchars($event_lieu, ENT_QUOTES, 'UTF-8');
        $date_publication = $date_programmee ?: date('Y-m-d H:i:s');

        try {
            $pdo = Config::getConnexion();
            $sql = "INSERT INTO forums (titre, contenu, categorie, image, statut, nb_vues, nb_likes, is_event, event_date, event_lieu, date_publication, id_user)
                    VALUES (:titre, :contenu, :categorie, :image, 'active', 0, 0, :is_event, :event_date, :event_lieu, :date_publication, :id_user)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([
                ':titre' => $titre,
                ':contenu' => $contenu,
                ':categorie' => $categorie,
                ':image' => $image,
                ':is_event' => $is_event,
                ':event_date' => $event_date,
                ':event_lieu' => $event_lieu,
                ':date_publication' => $date_publication,
                ':id_user' => $id_user
            ]);
            if ($success) {
                header("Location: publications.php?status=success&msg=" . urlencode("Publication ajoutée"));
                exit;
            } else {
                $errors[] = "Erreur lors de l'enregistrement en base.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur SQL : " . $e->getMessage();
        }
    }
    if (!empty($errors)) {
        $errorMessage = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une publication - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <style id="theme-style">
        body { background: #fff; color: #000; transition: 0.3s; }
        body.dark-mode { background: #1e1e2f; color: #eee; }
        body.dark-mode .card, body.dark-mode .pub-card, body.dark-mode .form-container { background: #2a2a3a; color: #eee; border-color: #444; }
        .form-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .required:after { content: " *"; color: red; }
        .error-msg { color: #dc3545; font-size: 0.875rem; margin-top: 5px; }
        .btn-primary { background: #435ebe; border: none; }
        .btn-primary:hover { background: #3348a8; }
        
    </style>
</head>
<body>
<button id="theme-toggle" class="btn btn-sm btn-secondary position-fixed bottom-0 end-0 m-3" style="z-index: 1000;">🌓 Thème</button>
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

<div class="container">
    <div class="form-container">
        <h2 class="mb-4">➕ Nouvelle publication</h2>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="titre" class="form-label required">Titre</label>
                <input type="text" class="form-control" id="titre" name="titre" required>
                <div class="error-msg" id="titreError"></div>
            </div>
            <div class="mb-3">
                <label for="contenu" class="form-label required">Contenu</label>
                <textarea class="form-control" id="contenu" name="contenu" rows="6" required></textarea>
                <div class="error-msg" id="contenuError"></div>
            </div>
            <div class="mb-3">
                <label for="categorie" class="form-label required">Catégorie</label>
                <select class="form-select" id="categorie" name="categorie">
                    <option value="general">Général</option>
                    <option value="stage">Stage</option>
                    <option value="job">Job</option>
                    <option value="question">Question</option>
                    <option value="evenement">Événement</option>
                </select>
                <div class="error-msg" id="categorieError"></div>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image (optionnelle)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                <small class="text-muted">JPG, PNG, GIF max 2 Mo</small>
                <div class="error-msg" id="imageError"></div>
            </div>
            <div class="mb-3">
                <label for="date_programmee" class="form-label">Programmer la publication</label>
                <input type="datetime-local" class="form-control" id="date_programmee" name="date_programmee">
                <small class="text-muted">Laissez vide pour publication immédiate</small>
                <div class="error-msg" id="dateProgError"></div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_event" name="is_event" value="1">
                <label class="form-check-label" for="is_event">C'est un événement</label>
            </div>
            <div id="eventFields" style="display: none;">
                <div class="mb-3">
                    <label for="event_date" class="form-label">Date de l'événement</label>
                    <input type="date" class="form-control" id="event_date" name="event_date">
                </div>
                <div class="mb-3">
                    <label for="event_lieu" class="form-label">Lieu</label>
                    <input type="text" class="form-control" id="event_lieu" name="event_lieu">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Publier</button>
            <a href="publications.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>

<script>
    document.getElementById('is_event').addEventListener('change', function() {
        document.getElementById('eventFields').style.display = this.checked ? 'block' : 'none';
    });
    function validateForm() {
        let isValid = true;
        document.querySelectorAll('.error-msg').forEach(el => el.innerHTML = '');
        let titre = document.getElementById('titre').value.trim();
        let contenu = document.getElementById('contenu').value.trim();
        let categorie = document.getElementById('categorie').value;
        let dateProg = document.getElementById('date_programmee').value;
        let image = document.getElementById('image').files[0];
        if (titre.length < 3) {
            document.getElementById('titreError').innerHTML = 'Titre trop court (min 3)';
            isValid = false;
        }
        if (contenu.length < 10) {
            document.getElementById('contenuError').innerHTML = 'Contenu trop court (min 10)';
            isValid = false;
        }
        if (!categorie) {
            document.getElementById('categorieError').innerHTML = 'Choisissez une catégorie';
            isValid = false;
        }
        if (dateProg && new Date(dateProg) <= new Date()) {
            document.getElementById('dateProgError').innerHTML = 'La date programmée doit être dans le futur';
            isValid = false;
        }
        if (image) {
            let allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowed.includes(image.type)) {
                document.getElementById('imageError').innerHTML = 'Format non autorisé';
                isValid = false;
            }
            if (image.size > 2 * 1024 * 1024) {
                document.getElementById('imageError').innerHTML = 'Image > 2 Mo';
                isValid = false;
            }
        }
        return isValid;
    }
    // Mode sombre
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
    document.getElementById('theme-toggle').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });
</script>
</body>
</html>