<?php
require_once("../../model/Pack.php");

$pack = new Pack();
$packs = $pack->getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Abonnement</title>
</head>
<body>

<h2>Choisir un Pack</h2>

<?php foreach($packs as $p): ?>
    <div style="border:1px solid black; padding:10px; margin:10px;">
        
        <h3><?php echo $p['nom-pack']; ?></h3>
        <p>Prix: <?php echo $p['prix']; ?></p>
        <p>Description: <?php echo $p['description']; ?></p>
        <p>Nb projets max: <?php echo $p['nb-proj-max']; ?></p>
        <p>Support: <?php echo $p['support-prioritaire']; ?></p>

        <form action="../../controller/AbonnementController.php" method="POST">
            <input type="hidden" name="pack_id" value="<?php echo $p['id-pack']; ?>">
            <button type="submit" name="subscribe">S'abonner</button>
        </form>

    </div>
<?php endforeach; ?>

</body>
</html>
