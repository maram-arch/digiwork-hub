<?php
require_once __DIR__ . '/../../controller/projectController.php';

$projetC = new ProjetC();
$listeProjets = $projetC->listProjets();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Projets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        h1 {
            color: #1b3f8b;
        }

        a.btn {
            display: inline-block;
            background: #1b3f8b;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #1b3f8b;
            color: white;
        }

        .edit {
            color: #0a7cff;
            text-decoration: none;
            font-weight: bold;
        }

        .delete {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Liste des Projets</h1>
    <a class="btn" href="projectCRUD.php">+ Ajouter un projet</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Discription</th>
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
                <td><?php echo htmlspecialchars($projet['statut']); ?></td>
                <td><?php echo htmlspecialchars($projet['id-user']); ?></td>
                <td><?php echo htmlspecialchars($projet['id-offre']); ?></td>
                <td>
                    <a class="edit" href="projectCRUD.php?edit=<?php echo $projet['id-projet']; ?>">Modifier</a> |
                    <a class="delete" href="projectCRUD.php?delete=<?php echo $projet['id-projet']; ?>" onclick="return confirm('Supprimer ce projet ?');">Supprimer</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>