<?php
require_once("../../model/Pack.php");
$pack = new Pack();
$packs = $pack->getAll();
?>

<h2>Packs</h2>

<?php foreach($packs as $p): ?>
<div>
<h3><?= $p['nom-pack'] ?></h3>
<p><?= $p['prix'] ?></p>

<form action="../../controller/AbonnementController.php" method="POST">
<input type="hidden" name="pack_id" value="<?= $p['id-pack'] ?>">
<button name="subscribe">S'abonner</button>
</form>
</div>
<?php endforeach; ?>
