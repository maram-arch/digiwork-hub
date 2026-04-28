<?php
require_once __DIR__ . '/../../config2.php';

$db = config::getConnexion();
$error = "";

function formatStatut($statut) {
    switch ($statut) {
        case 'en_attente': return 'En attente';
        case 'en_cours': return 'En cours';
        case 'termine': return 'Terminé';
        case 'annule': return 'Annulé';
        default: return $statut;
    }
}

/* =========================
   PROJECT CRUD
========================= */

/* DELETE PROJECT */
if (isset($_GET['delete'])) {
    try {
        $sql = "DELETE FROM projet WHERE `id-projet` = :id";
        $query = $db->prepare($sql);
        $query->execute(['id' => $_GET['delete']]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression projet : " . $e->getMessage();
    }
}

/* ADD PROJECT */
if (isset($_POST['add'])) {
    try {
        $sql = "INSERT INTO projet (`titre`, `discription`, `budget`, `statut`, `id-user`, `id-offre`)
                VALUES (:titre, :discription, :budget, :statut, :id_user, :id_offre)";

        $query = $db->prepare($sql);
        $query->execute([
            'titre' => trim($_POST['titre']),
            'discription' => trim($_POST['discription']),
            'budget' => str_replace(',', '.', $_POST['budget']),
            'statut' => $_POST['statut'],
            'id_user' => $_POST['id_user'],
            'id_offre' => $_POST['id_offre']
        ]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur ajout projet : " . $e->getMessage();
    }
}

/* UPDATE PROJECT */
if (isset($_POST['update'])) {
    try {
        $sql = "UPDATE projet SET
                    `titre` = :titre,
                    `discription` = :discription,
                    `budget` = :budget,
                    `statut` = :statut,
                    `id-user` = :id_user,
                    `id-offre` = :id_offre
                WHERE `id-projet` = :id";

        $query = $db->prepare($sql);
        $query->execute([
            'id' => $_POST['id_projet'],
            'titre' => trim($_POST['titre']),
            'discription' => trim($_POST['discription']),
            'budget' => str_replace(',', '.', $_POST['budget']),
            'statut' => $_POST['statut'],
            'id_user' => $_POST['id_user'],
            'id_offre' => $_POST['id_offre']
        ]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur modification projet : " . $e->getMessage();
    }
}

/* EDIT PROJECT */
$editProjet = null;

if (isset($_GET['edit'])) {
    $sql = "SELECT * FROM projet WHERE `id-projet` = :id";
    $query = $db->prepare($sql);
    $query->execute(['id' => $_GET['edit']]);
    $editProjet = $query->fetch(PDO::FETCH_ASSOC);
}

/* LIST PROJECTS */
$listeProjets = $db->query("SELECT * FROM projet ORDER BY `id-projet` DESC")->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   SPONSOR CRUD
========================= */

/* DELETE SPONSOR */
if (isset($_GET['delete_sponsor'])) {
    try {
        $sql = "DELETE FROM sponsor WHERE id_user = :id_user";
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $_GET['delete_sponsor']]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression sponsor : " . $e->getMessage();
    }
}

/* ADD SPONSOR */
if (isset($_POST['add_sponsor'])) {
    try {
        $sql = "INSERT INTO sponsor (id_user, nom, type, adresse, discription)
                VALUES (:id_user, :nom, :type, :adresse, :discription)";

        $query = $db->prepare($sql);
        $query->execute([
            'id_user' => $_POST['sponsor_id_user'],
            'nom' => trim($_POST['nom']),
            'type' => trim($_POST['type']),
            'adresse' => trim($_POST['adresse']),
            'discription' => trim($_POST['sponsor_discription'])
        ]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur ajout sponsor : " . $e->getMessage();
    }
}

/* UPDATE SPONSOR */
if (isset($_POST['update_sponsor'])) {
    try {
        $sql = "UPDATE sponsor SET
                    nom = :nom,
                    type = :type,
                    adresse = :adresse,
                    discription = :discription
                WHERE id_user = :id_user";

        $query = $db->prepare($sql);
        $query->execute([
            'id_user' => $_POST['sponsor_id_user'],
            'nom' => trim($_POST['nom']),
            'type' => trim($_POST['type']),
            'adresse' => trim($_POST['adresse']),
            'discription' => trim($_POST['sponsor_discription'])
        ]);

        header("Location: projectCRUD.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur modification sponsor : " . $e->getMessage();
    }
}

/* EDIT SPONSOR */
$editSponsor = null;

if (isset($_GET['edit_sponsor'])) {
    $sql = "SELECT * FROM sponsor WHERE id_user = :id_user";
    $query = $db->prepare($sql);
    $query->execute(['id_user' => $_GET['edit_sponsor']]);
    $editSponsor = $query->fetch(PDO::FETCH_ASSOC);
}

/* LIST SPONSORS */
$listeSponsors = $db->query("SELECT * FROM sponsor ORDER BY id_user DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CRUD Projet & Sponsor</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        h1, h2 {
            color: #1b3f8b;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d6dce5;
            border-radius: 8px;
            font-size: 14px;
        }

        textarea {
            min-height: 90px;
        }

        button {
            margin-top: 15px;
            background: #1b3f8b;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #15316d;
        }

        .cancel-btn {
            background: #888;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 10px;
        }

        .back-link {
            color: #1b3f8b;
            font-weight: bold;
            text-decoration: none;
        }

        .error {
            color: red;
            font-weight: bold;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background: #1b3f8b;
            color: white;
        }

        .edit-link {
            color: #0a7cff;
            text-decoration: none;
            font-weight: bold;
        }

        .delete-link {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .section-separator {
            margin: 45px 0 25px;
            border-top: 3px solid #1b3f8b;
        }
    </style>
</head>

<body>

<a class="back-link" href="projectList.php">← Retour à la liste</a>

<h1>Gestion des Projets & Sponsors</h1>

<?php if (!empty($error)) { ?>
    <p class="error"><?php echo $error; ?></p>
<?php } ?>

<!-- =========================
     PROJECT FORM
========================= -->

<div class="card">
    <?php if ($editProjet) { ?>

        <h2>Modifier un projet</h2>

        <form method="POST" onsubmit="return validateProjetForm(event);">
            <input type="hidden" name="id_projet" value="<?php echo $editProjet['id-projet']; ?>">

            <label>Titre</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($editProjet['titre']); ?>" required>

            <label>Description</label>
            <textarea name="discription" required><?php echo htmlspecialchars($editProjet['discription']); ?></textarea>

            <label>Budget</label>
            <input type="text" name="budget" value="<?php echo htmlspecialchars($editProjet['budget']); ?>" required>

            <label>Statut</label>
            <select name="statut" required>
                <option value="en_attente" <?php if ($editProjet['statut'] == 'en_attente') echo 'selected'; ?>>En attente</option>
                <option value="en_cours" <?php if ($editProjet['statut'] == 'en_cours') echo 'selected'; ?>>En cours</option>
                <option value="termine" <?php if ($editProjet['statut'] == 'termine') echo 'selected'; ?>>Terminé</option>
                <option value="annule" <?php if ($editProjet['statut'] == 'annule') echo 'selected'; ?>>Annulé</option>
            </select>

            <label>ID User</label>
            <input type="number" name="id_user" value="<?php echo htmlspecialchars($editProjet['id-user']); ?>" required>

            <label>ID Offre</label>
            <input type="number" name="id_offre" value="<?php echo htmlspecialchars($editProjet['id-offre']); ?>" required>

            <button type="submit" name="update">Modifier Projet</button>
            <a class="cancel-btn" href="projectCRUD.php">Annuler</a>
        </form>

    <?php } else { ?>

        <h2>Ajouter un projet</h2>

        <form method="POST" onsubmit="return validateProjetForm(event);">
            <label>Titre</label>
            <input type="text" name="titre" required>

            <label>Description</label>
            <textarea name="discription" required></textarea>

            <label>Budget</label>
            <input type="text" name="budget" required>

            <label>Statut</label>
            <select name="statut" required>
                <option value="en_attente">En attente</option>
                <option value="en_cours">En cours</option>
                <option value="termine">Terminé</option>
                <option value="annule">Annulé</option>
            </select>

            <label>ID User</label>
            <input type="number" name="id_user" required>

            <label>ID Offre</label>
            <input type="number" name="id_offre" required>

            <button type="submit" name="add">Ajouter Projet</button>
        </form>

    <?php } ?>
</div>

<!-- =========================
     PROJECT TABLE
========================= -->

<div class="card">
    <h2>Liste des projets</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Description</th>
            <th>Budget</th>
            <th>Statut</th>
            <th>ID User</th>
            <th>ID Offre</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($listeProjets as $projet) { ?>
            <tr>
                <td><?php echo $projet['id-projet']; ?></td>
                <td><?php echo htmlspecialchars($projet['titre']); ?></td>
                <td><?php echo htmlspecialchars($projet['discription']); ?></td>
                <td><?php echo htmlspecialchars($projet['budget']); ?></td>
                <td><?php echo htmlspecialchars(formatStatut($projet['statut'])); ?></td>
                <td><?php echo htmlspecialchars($projet['id-user']); ?></td>
                <td><?php echo htmlspecialchars($projet['id-offre']); ?></td>
                <td>
                    <a class="edit-link" href="projectCRUD.php?edit=<?php echo $projet['id-projet']; ?>">Modifier</a> |
                    <a class="delete-link" href="projectCRUD.php?delete=<?php echo $projet['id-projet']; ?>"
                       onclick="return confirm('Supprimer ce projet ?');">Supprimer</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div class="section-separator"></div>

<!-- =========================
     SPONSOR FORM
========================= -->

<div class="card">
    <?php if ($editSponsor) { ?>

        <h2>Modifier un sponsor</h2>

        <form method="POST" onsubmit="return validateSponsorForm(event);">
            <input type="hidden" name="sponsor_id_user" value="<?php echo $editSponsor['id_user']; ?>">

            <label>Nom</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($editSponsor['nom']); ?>" required>

            <label>Type</label>
            <input type="text" name="type" value="<?php echo htmlspecialchars($editSponsor['type']); ?>" required>

            <label>Adresse</label>
            <input type="text" name="adresse" value="<?php echo htmlspecialchars($editSponsor['adresse']); ?>" required>

            <label>Description</label>
            <textarea name="sponsor_discription" required><?php echo htmlspecialchars($editSponsor['discription']); ?></textarea>

            <button type="submit" name="update_sponsor">Modifier Sponsor</button>
            <a class="cancel-btn" href="projectCRUD.php">Annuler</a>
        </form>

    <?php } else { ?>

        <h2>Ajouter un sponsor</h2>

        <form method="POST" onsubmit="return validateSponsorForm(event);">
            <label>ID User</label>
            <input type="number" name="sponsor_id_user" required>

            <label>Nom</label>
            <input type="text" name="nom" required>

            <label>Type</label>
            <input type="text" name="type" required>

            <label>Adresse</label>
            <input type="text" name="adresse" required>

            <label>Description</label>
            <textarea name="sponsor_discription" required></textarea>

            <button type="submit" name="add_sponsor">Ajouter Sponsor</button>
        </form>

    <?php } ?>
</div>

<!-- =========================
     SPONSOR TABLE
========================= -->

<div class="card">
    <h2>Liste des sponsors</h2>

    <table>
        <tr>
            <th>ID User</th>
            <th>Nom</th>
            <th>Type</th>
            <th>Adresse</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($listeSponsors as $sponsor) { ?>
            <tr>
                <td><?php echo htmlspecialchars($sponsor['id_user']); ?></td>
                <td><?php echo htmlspecialchars($sponsor['nom']); ?></td>
                <td><?php echo htmlspecialchars($sponsor['type']); ?></td>
                <td><?php echo htmlspecialchars($sponsor['adresse']); ?></td>
                <td><?php echo htmlspecialchars($sponsor['discription']); ?></td>
                <td>
                    <a class="edit-link" href="projectCRUD.php?edit_sponsor=<?php echo $sponsor['id_user']; ?>">Modifier</a> |
                    <a class="delete-link" href="projectCRUD.php?delete_sponsor=<?php echo $sponsor['id_user']; ?>"
                       onclick="return confirm('Supprimer ce sponsor ?');">Supprimer</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<script>
function validateProjetForm(event) {
    const form = event.target;

    const titre = form.querySelector('[name="titre"]').value.trim();
    const discription = form.querySelector('[name="discription"]').value.trim();
    const budget = form.querySelector('[name="budget"]').value.trim().replace(',', '.');
    const idUser = form.querySelector('[name="id_user"]').value.trim();
    const idOffre = form.querySelector('[name="id_offre"]').value.trim();

    const titreWords = titre.split(/\s+/).filter(Boolean);

    if (titre === "") {
        alert("Le titre ne doit pas être vide.");
        return false;
    }

    if (/^\d+$/.test(titre)) {
        alert("Le titre ne doit pas contenir uniquement des chiffres.");
        return false;
    }

    if (!/^[a-zA-ZÀ-ÿ0-9\s'-]+$/.test(titre)) {
        alert("Le titre doit contenir seulement des lettres, chiffres, espaces, apostrophes ou tirets.");
        return false;
    }

    if (titreWords.length > 20) {
        alert("Le titre ne doit pas dépasser 20 mots.");
        return false;
    }

    if (discription === "") {
        alert("La description ne doit pas être vide.");
        return false;
    }

    if (discription.length < 20) {
        alert("La description doit contenir au moins 20 caractères, espaces inclus.");
        return false;
    }

    if (budget === "") {
        alert("Le budget ne doit pas être vide.");
        return false;
    }

    if (isNaN(budget)) {
        alert("Le budget doit contenir uniquement des nombres.");
        return false;
    }

    if (Number(budget) < 0) {
        alert("Le budget ne peut pas être négatif.");
        return false;
    }

    if (!/^\d+(\.\d{1,2})?$/.test(budget)) {
        alert("Le budget doit être un nombre valide avec maximum 2 chiffres après la virgule.");
        return false;
    }

    if (idUser === "" || !/^\d+$/.test(idUser) || Number(idUser) <= 0 || idUser.length < 8 || idUser.length > 12) {
        alert("L'ID User doit être un entier positif entre 8 et 12 chiffres.");
        return false;
    }

    if (idOffre === "" || !/^\d+$/.test(idOffre) || Number(idOffre) <= 0 || idOffre.length < 8 || idOffre.length > 12) {
        alert("L'ID Offre doit être un entier positif entre 8 et 12 chiffres.");
        return false;
    }

    return true;
}

function validateSponsorForm(event) {
    const form = event.target;

    const idUserInput = form.querySelector('[name="sponsor_id_user"]');
    const idUser = idUserInput ? idUserInput.value.trim() : "";
    const nom = form.querySelector('[name="nom"]').value.trim();
    const type = form.querySelector('[name="type"]').value.trim();
    const adresse = form.querySelector('[name="adresse"]').value.trim();
    const discription = form.querySelector('[name="sponsor_discription"]').value.trim();

    if (idUserInput && (idUser === "" || !/^\d+$/.test(idUser) || Number(idUser) <= 0 || idUser.length < 8 || idUser.length > 12)) {
        alert("L'ID User du sponsor doit être un entier positif entre 8 et 12 chiffres.");
        return false;
    }

    if (nom === "") {
        alert("Le nom du sponsor ne doit pas être vide.");
        return false;
    }

    if (/^\d+$/.test(nom)) {
        alert("Le nom du sponsor ne doit pas contenir uniquement des chiffres.");
        return false;
    }

    if (!/^[a-zA-ZÀ-ÿ0-9\s'-]+$/.test(nom)) {
        alert("Le nom du sponsor contient des caractères invalides.");
        return false;
    }

    if (type === "") {
        alert("Le type du sponsor ne doit pas être vide.");
        return false;
    }

    if (/^\d+$/.test(type)) {
        alert("Le type du sponsor ne doit pas contenir uniquement des chiffres.");
        return false;
    }

   if (adresse === "") {
    alert("L'adresse ne doit pas être vide.");
    return false;
}

if (adresse.length < 10) {
    alert("L'adresse doit contenir au moins 10 caractères.");
    return false;
}

if (adresse.length > 50) {
    alert("L'adresse ne doit pas dépasser 50 caractères.");
    return false;
}

    if (discription.length < 20) {
        alert("La description du sponsor doit contenir au moins 20 caractères.");
        return false;
    }

    return true;
}
</script>

</body>
</html>