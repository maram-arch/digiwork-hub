<?php
require_once __DIR__ . '/../../../controller/projectController.php';

$projetC = new ProjetC();

$projectStats = $projetC->getProjectStatsByStatus();
$sponsorStats = $projetC->getMostSponsoredStats();
$historique = $projetC->listHistorique();

$sort = $_GET['sort'] ?? 'id-projet';
$direction = $_GET['direction'] ?? 'ASC';
$searchId = $_GET['searchId'] ?? '';
$listeProjets = $projetC->listProjets($sort, $direction, $searchId);

$sortSponsor = $_GET['sortSponsor'] ?? 'id_user';
$directionSponsor = $_GET['directionSponsor'] ?? 'ASC';
$searchSponsorId = $_GET['searchSponsorId'] ?? '';
$listeSponsors = $projetC->listSponsors($sortSponsor, $directionSponsor, $searchSponsorId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Projets et Sponsors</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        h1, h2 {
            color: #1b3f8b;
        }

        .btn {
            display: inline-block;
            background: #1b3f8b;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        form {
            margin-bottom: 20px;
        }

        input, select, button {
            padding: 7px;
            font-size: 15px;
            margin: 3px;
        }

        button {
            background: #1b3f8b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 8px 12px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 50px;
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

        hr {
            margin: 40px 0;
            border: 1px solid #1b3f8b;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .popup-content {
            background: white;
            width: 600px;
            margin: 80px auto;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            max-height: 80vh;
            overflow-y: auto;
        }

        .historique-content {
            width: 85%;
        }
    </style>
</head>

<body>

<h1>Liste des Projets</h1>

<a class="btn" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD">
    + Ajouter un projet
</a>

<form method="GET" action="/DigiWorkHub/digiwork-hub/index2.php">
    <input type="hidden" name="page" value="listProject">

    <input type="number" name="searchId"
           placeholder="Rechercher par ID projet"
           value="<?= htmlspecialchars($searchId); ?>">

    <button type="submit" name="action" value="search">🔍 Rechercher</button>

    <br><br>

    <select name="sort">
        <option value="id-projet" <?= ($sort == 'id-projet') ? 'selected' : '' ?>>ID</option>
        <option value="budget" <?= ($sort == 'budget') ? 'selected' : '' ?>>Budget</option>
        <option value="titre" <?= ($sort == 'titre') ? 'selected' : '' ?>>Titre</option>
        <option value="id-user" <?= ($sort == 'id-user') ? 'selected' : '' ?>>ID User</option>
        <option value="id-offre" <?= ($sort == 'id-offre') ? 'selected' : '' ?>>ID Offre</option>
        <option value="statut" <?= ($sort == 'statut') ? 'selected' : '' ?>>Statut</option>
    </select>

    <select name="direction">
        <option value="ASC" <?= ($direction == 'ASC') ? 'selected' : '' ?>>Croissant</option>
        <option value="DESC" <?= ($direction == 'DESC') ? 'selected' : '' ?>>Décroissant</option>
    </select>

    <button type="submit" name="action" value="sort">🔽 Trier</button>
</form>

<button onclick="openProjectStats()" type="button">Statistique projets</button>
<button onclick="openHistorique()" type="button">Historique</button>
<a href="/DigiWorkHub/digiwork-hub/exportProjectsSponsorsPDF.php" class="btn">
    Exporter PDF
</a>

<div id="projectStatsPopup" class="popup">
    <div class="popup-content">
        <h2>Statistiques des projets</h2>

        <?php
        $totalProjectStats = array_sum(array_column($projectStats, 'total'));
        $colors = ['#ff4b5c', '#ffd93d', '#4d96ff', '#6bcb77'];
        $start = 0;
        $gradientParts = [];
        ?>

        <?php foreach ($projectStats as $index => $stat) {
            $percent = ($totalProjectStats > 0) ? round(($stat['total'] / $totalProjectStats) * 100, 1) : 0;
            $end = $start + $percent;
            $color = $colors[$index % count($colors)];
            $gradientParts[] = "$color $start% $end%";
            $start = $end;
        } ?>

        <div style="
            width:250px;
            height:250px;
            border-radius:50%;
            margin:20px auto;
            background:conic-gradient(<?= implode(',', $gradientParts); ?>);
        "></div>

        <?php foreach ($projectStats as $index => $stat) {
            $percent = ($totalProjectStats > 0) ? round(($stat['total'] / $totalProjectStats) * 100, 1) : 0;
            $color = $colors[$index % count($colors)];
        ?>
            <p>
                <span style="display:inline-block; width:15px; height:15px; background:<?= $color; ?>;"></span>
                <?= htmlspecialchars($stat['statut']); ?> : <?= $percent; ?>%
            </p>
        <?php } ?>

        <button onclick="closeProjectStats()" type="button">Fermer</button>
    </div>
</div>

<div id="historiquePopup" class="popup">
    <div class="popup-content historique-content">
        <h2>Historique</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Action</th>
                <th>Entité</th>
                <th>Description</th>
                <th>Date</th>
            </tr>

            <?php foreach ($historique as $h) { ?>
                <tr>
                    <td><?= htmlspecialchars($h['id_historique']); ?></td>
                    <td><?= htmlspecialchars($h['action']); ?></td>
                    <td><?= htmlspecialchars($h['entite']); ?></td>
                    <td><?= htmlspecialchars($h['description']); ?></td>
                    <td><?= htmlspecialchars($h['date_action']); ?></td>
                </tr>
            <?php } ?>
        </table>

        <button onclick="closeHistorique()" type="button">Fermer</button>
    </div>
</div>

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
            <td><?= htmlspecialchars($projet['id-projet']); ?></td>
            <td><?= htmlspecialchars($projet['titre']); ?></td>
            <td><?= htmlspecialchars($projet['discription']); ?></td>
            <td><?= htmlspecialchars($projet['budget']); ?></td>
            <td><?= htmlspecialchars($projet['statut']); ?></td>
            <td><?= htmlspecialchars($projet['id-user']); ?></td>
            <td><?= htmlspecialchars($projet['id-offre']); ?></td>
            <td>
                <a class="edit" href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&edit=<?= $projet['id-projet']; ?>">
                    Modifier
                </a>
                |
                <a class="delete"
                   href="/DigiWorkHub/digiwork-hub/index2.php?page=projectCRUD&delete=<?= $projet['id-projet']; ?>"
                   onclick="return confirm('Supprimer ce projet ?');">
                    Supprimer
                </a>
            </td>
        </tr>
    <?php } ?>
</table>

<hr>

<h2>Liste des Sponsorships</h2>

<form method="GET" action="/DigiWorkHub/digiwork-hub/index2.php">
    <input type="hidden" name="page" value="listProject">

    <input type="number" name="searchSponsorId"
           placeholder="Rechercher par ID User"
           value="<?= htmlspecialchars($searchSponsorId); ?>">

    <button type="submit" name="actionSponsor" value="search">🔍 Rechercher</button>

    <br><br>

    <select name="sortSponsor">
        <option value="id_user" <?= ($sortSponsor == 'id_user') ? 'selected' : '' ?>>ID User</option>
        <option value="nom" <?= ($sortSponsor == 'nom') ? 'selected' : '' ?>>Nom</option>
        <option value="type" <?= ($sortSponsor == 'type') ? 'selected' : '' ?>>Type</option>
    </select>

    <select name="directionSponsor">
        <option value="ASC" <?= ($directionSponsor == 'ASC') ? 'selected' : '' ?>>Croissant</option>
        <option value="DESC" <?= ($directionSponsor == 'DESC') ? 'selected' : '' ?>>Décroissant</option>
    </select>

    <button type="submit" name="actionSponsor" value="sort">🔽 Trier</button>
</form>

<button onclick="openSponsorStats()" type="button">Statistique sponsorships</button>

<div id="sponsorStatsPopup" class="popup">
    <div class="popup-content">
        <h2>Statistiques des sponsorships</h2>

        <?php
        $totalSponsorStats = array_sum(array_column($sponsorStats, 'total'));
        $colorsSponsor = ['#ff4b5c', '#ffd93d', '#4d96ff', '#6bcb77', '#845ec2'];
        $startSponsor = 0;
        $gradientSponsorParts = [];
        ?>

        <?php foreach ($sponsorStats as $index => $stat) {
            $percent = ($totalSponsorStats > 0) ? round(($stat['total'] / $totalSponsorStats) * 100, 1) : 0;
            $endSponsor = $startSponsor + $percent;
            $color = $colorsSponsor[$index % count($colorsSponsor)];
            $gradientSponsorParts[] = "$color $startSponsor% $endSponsor%";
            $startSponsor = $endSponsor;
        } ?>

        <div style="
            width:250px;
            height:250px;
            border-radius:50%;
            margin:20px auto;
            background:conic-gradient(<?= implode(',', $gradientSponsorParts); ?>);
        "></div>

        <?php foreach ($sponsorStats as $index => $stat) {
            $percent = ($totalSponsorStats > 0) ? round(($stat['total'] / $totalSponsorStats) * 100, 1) : 0;
            $color = $colorsSponsor[$index % count($colorsSponsor)];
        ?>
            <p>
                <span style="display:inline-block; width:15px; height:15px; background:<?= $color; ?>;"></span>
                <?= htmlspecialchars($stat['nom']); ?> : <?= $percent; ?>%
            </p>
        <?php } ?>

        <button onclick="closeSponsorStats()" type="button">Fermer</button>
    </div>
</div>

<table>
    <tr>
        <th>ID User</th>
        <th>Nom</th>
        <th>Type</th>
        <th>Adresse</th>
        <th>Description</th>
    </tr>

    <?php foreach ($listeSponsors as $sponsor) { ?>
        <tr>
            <td><?= htmlspecialchars($sponsor['id_user']); ?></td>
            <td><?= htmlspecialchars($sponsor['nom']); ?></td>
            <td><?= htmlspecialchars($sponsor['type']); ?></td>
            <td><?= htmlspecialchars($sponsor['adresse']); ?></td>
            <td><?= htmlspecialchars($sponsor['discription']); ?></td>
        </tr>
    <?php } ?>
</table>

<script>
function openProjectStats() {
    document.getElementById("projectStatsPopup").style.display = "block";
}

function closeProjectStats() {
    document.getElementById("projectStatsPopup").style.display = "none";
}

function openSponsorStats() {
    document.getElementById("sponsorStatsPopup").style.display = "block";
}

function closeSponsorStats() {
    document.getElementById("sponsorStatsPopup").style.display = "none";
}

function openHistorique() {
    document.getElementById("historiquePopup").style.display = "block";
}

function closeHistorique() {
    document.getElementById("historiquePopup").style.display = "none";
}
</script>

</body>
</html>