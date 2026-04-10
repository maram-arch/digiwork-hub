<?php
require_once("../config/config.php");

class Pack {

    function getAll() {
        global $pdo;
        return $pdo->query("SELECT * FROM `pack`");
    }

    function add($nom, $prix, $duree, $desc, $nb, $support) {
        global $pdo;

        $sql = "INSERT INTO `pack`
        (`nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`)
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prix, $duree, $desc, $nb, $support]);
    }

    function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM `pack` WHERE `id-pack`=?");
        $stmt->execute([$id]);
    }
}
?>
