<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/projectController.php';

$db = config::getConnexion();
$projetC = new ProjetC();
$error = "";

function formatStatut($statut)
{
    return match ($statut) {
        'en_attente' => 'En attente',
        'en_cours' => 'En cours',
        'termine' => 'Terminé',
        'annule' => 'Annulé',
        default => $statut
    };
}

/* DELETE PROJECT */
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];

        $sql = "DELETE FROM projet WHERE `id-projet` = :id";
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);

        $projetC->addHistorique(
            "Suppression",
            "Projet",
            "Le projet avec ID " . $id . " a été supprimé."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression projet : " . $e->getMessage();
    }
}

/* ADD PROJECT */
if (isset($_POST['add'])) {
    try {
        $titre = trim($_POST['titre']);

        // Use session user_id — never trust a manually entered user ID
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

        // Use provided id_offre if valid, otherwise default to 1
        $id_offre = isset($_POST['id_offre']) && (int)$_POST['id_offre'] > 0
            ? (int)$_POST['id_offre']
            : 1;

        $sql = "INSERT INTO projet (`titre`, `discription`, `budget`, `statut`, `id-user`, `id-offre`)
                VALUES (:titre, :discription, :budget, :statut, :id_user, :id_offre)";

        $query = $db->prepare($sql);
        $query->execute([
            'titre'       => $titre,
            'discription' => trim($_POST['discription']),
            'budget'      => str_replace(',', '.', $_POST['budget']),
            'statut'      => $_POST['statut'],
            'id_user'     => $id_user,
            'id_offre'    => $id_offre
        ]);

        $projetC->addHistorique(
            "Ajout",
            "Projet",
            "Le projet '" . $titre . "' a été ajouté."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
        exit;
    } catch (Exception $e) {
        $error = "Erreur ajout projet : " . $e->getMessage();
    }
}

/* UPDATE PROJECT */
if (isset($_POST['update'])) {
    try {
        $titre = trim($_POST['titre']);

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
            'id'          => $_POST['id_projet'],
            'titre'       => $titre,
            'discription' => trim($_POST['discription']),
            'budget'      => str_replace(',', '.', $_POST['budget']),
            'statut'      => $_POST['statut'],
            'id_user'     => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1,
            'id_offre'    => isset($_POST['id_offre']) && (int)$_POST['id_offre'] > 0 ? (int)$_POST['id_offre'] : 1
        ]);

        $projetC->addHistorique(
            "Modification",
            "Projet",
            "Le projet '" . $titre . "' a été modifié."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
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


/* DELETE SPONSOR */
if (isset($_GET['delete_sponsor'])) {
    try {
        $idUser = $_GET['delete_sponsor'];

        $sql = "DELETE FROM sponsor WHERE id_user = :id_user";
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $idUser]);

        $projetC->addHistorique(
            "Suppression",
            "Sponsorship",
            "Le sponsorship avec ID User " . $idUser . " a été supprimé."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression sponsor : " . $e->getMessage();
    }
}

/* ADD SPONSOR */
if (isset($_POST['add_sponsor'])) {
    try {
        $nom = trim($_POST['nom']);

        $sql = "INSERT INTO sponsor (id_user, nom, type, adresse, discription)
                VALUES (:id_user, :nom, :type, :adresse, :discription)";

        $query = $db->prepare($sql);
        $query->execute([
            'id_user' => $_POST['sponsor_id_user'],
            'nom' => $nom,
            'type' => trim($_POST['type']),
            'adresse' => trim($_POST['adresse']),
            'discription' => trim($_POST['sponsor_discription'])
        ]);

        $projetC->addHistorique(
            "Ajout",
            "Sponsorship",
            "Le sponsorship '" . $nom . "' a été ajouté."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
        exit;
    } catch (Exception $e) {
        $error = "Erreur ajout sponsor : " . $e->getMessage();
    }
}

/* UPDATE SPONSOR */
if (isset($_POST['update_sponsor'])) {
    try {
        $nom = trim($_POST['nom']);

        $sql = "UPDATE sponsor SET
                    nom = :nom,
                    type = :type,
                    adresse = :adresse,
                    discription = :discription
                WHERE id_user = :id_user";

        $query = $db->prepare($sql);
        $query->execute([
            'id_user' => $_POST['sponsor_id_user'],
            'nom' => $nom,
            'type' => trim($_POST['type']),
            'adresse' => trim($_POST['adresse']),
            'discription' => trim($_POST['sponsor_discription'])
        ]);

        $projetC->addHistorique(
            "Modification",
            "Sponsorship",
            "Le sponsorship '" . $nom . "' a été modifié."
        );

        header("Location: /DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD");
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
            border: 1px solid #ccd3dd;
            border-radius: 7px;
            font-size: 15px;
        }

        textarea {
            height: 90px;
        }

        button {
            margin-top: 15px;
            background: #1b3f8b;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 7px;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #1b3f8b;
            color: white;
            padding: 12px;
        }

        td {
            border: 1px solid #ddd;
            padding: 11px;
            text-align: center;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1b3f8b;
            font-weight: bold;
            text-decoration: none;
        }

        .cancel-btn {
            display: inline-block;
            margin-left: 10px;
            background: #777;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }

        .edit-link {
            color: #0066ff;
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

<a class="back-link" href="/DigiWorkHub/digiwork-hub/index2.php?page=listProject">← Retour à la liste</a>

<h1>Gestion des Projets & Sponsors</h1>

<?php if (!empty($error)) { ?>
    <p class="error"><?php echo $error; ?></p>
<?php } ?>

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
            <a class="cancel-btn" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD">Annuler</a>
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
                <td><?php echo htmlspecialchars($projet['id-projet']); ?></td>
                <td><?php echo htmlspecialchars($projet['titre']); ?></td>
                <td><?php echo htmlspecialchars($projet['discription']); ?></td>
                <td><?php echo htmlspecialchars($projet['budget']); ?></td>
                <td><?php echo htmlspecialchars(formatStatut($projet['statut'])); ?></td>
                <td><?php echo htmlspecialchars($projet['id-user']); ?></td>
                <td><?php echo htmlspecialchars($projet['id-offre']); ?></td>
                <td>
                    <a class="edit-link" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&edit=<?php echo $projet['id-projet']; ?>">Modifier</a> |
                    <a class="delete-link" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&delete=<?php echo $projet['id-projet']; ?>"
                       onclick="return confirm('Supprimer ce projet ?');">Supprimer</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div class="section-separator"></div>

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
            <a class="cancel-btn" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD">Annuler</a>
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
                    <a class="edit-link" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&edit_sponsor=<?php echo $sponsor['id_user']; ?>">Modifier</a> |
                    <a class="delete-link" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&delete_sponsor=<?php echo $sponsor['id_user']; ?>"
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

    if (discription.length < 10) {
        alert("La description doit contenir au moins 10 caractères.");
        return false;
    }

    if (isNaN(budget) || Number(budget) <= 0) {
        alert("Le budget doit être un nombre positif.");
        return false;
    }

    if (idUser === "" || Number(idUser) <= 0) {
        alert("ID User doit être un nombre positif.");
        return false;
    }

    if (idOffre === "" || Number(idOffre) <= 0) {
        alert("ID Offre doit être un nombre positif.");
        return false;
    }

    return true;
}

function validateSponsorForm(event) {
    const form = event.target;

    const idUser = form.querySelector('[name="sponsor_id_user"]').value.trim();
    const nom = form.querySelector('[name="nom"]').value.trim();
    const type = form.querySelector('[name="type"]').value.trim();
    const adresse = form.querySelector('[name="adresse"]').value.trim();
    const description = form.querySelector('[name="sponsor_discription"]').value.trim();

    if (idUser === "" || Number(idUser) <= 0) {
        alert("ID User doit être un nombre positif.");
        return false;
    }

    if (nom.length < 2) {
        alert("Le nom doit contenir au moins 2 caractères.");
        return false;
    }

    if (type.length < 2) {
        alert("Le type doit contenir au moins 2 caractères.");
        return false;
    }

    if (adresse.length < 3) {
        alert("L'adresse doit contenir au moins 3 caractères.");
        return false;
    }

    if (description.length < 10) {
        alert("La description doit contenir au moins 10 caractères.");
        return false;
    }

    return true;
}
</script>

</body>
</html>