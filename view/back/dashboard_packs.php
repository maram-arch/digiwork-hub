<?php
require_once("../../model/Pack.php");
$pack = new Pack();
$packs = $pack->getAll();
?>

<h2>Gestion Packs</h2>

<form action="../../controller/PackController.php" method="POST">
<input type="text" name="nom" placeholder="Nom">
<input type="number" name="prix" placeholder="Prix">
<input type="date" name="duree">
<textarea name="description"></textarea>
<input type="number" name="nb">
<select name="support">
<option>oui</option>
<option>non</option>
</select>
<button name="add">Ajouter</button>
</form>

<?php foreach($packs as $p): ?>
<div>
<?= $p['nom-pack'] ?> - <?= $p['prix'] ?>
<a href="../../controller/PackController.php?delete=<?= $p['id-pack'] ?>">Supprimer</a>
</div>
<?php endforeach; ?>
