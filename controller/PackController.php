<?php
require_once("../model/Pack.php");

$pack = new Pack();

if(isset($_POST['add'])) {
    $pack->add(
        $_POST['nom'],
        $_POST['prix'],
        $_POST['duree'],
        $_POST['description'],
        $_POST['nb'],
        $_POST['support']
    );
    header("Location: ../view/back/dashboard_packs.php");
}

if(isset($_GET['delete'])) {
    $pack->delete($_GET['delete']);
    header("Location: ../view/back/dashboard_packs.php");
}
?>
