<?php
require_once("../model/Abonnement.php");

$abonnement = new Abonnement();

if(isset($_POST['subscribe'])) {
    $abonnement->subscribe(1, $_POST['pack_id']);
    header("Location: ../view/front/packs.php");
}
?>
