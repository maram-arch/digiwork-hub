<?php
require_once(__DIR__ . "/../config/config.php");

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

    function getById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM `pack` WHERE `id-pack`=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function update($id, $nom, $prix, $duree, $desc, $nb, $support) {
        global $pdo;
        $sql = "UPDATE `pack` SET `nom-pack`=?, `prix`=?, `duree`=?, `description`=?, `nb-proj-max`=?, `support-prioritaire`=? WHERE `id-pack`=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prix, $duree, $desc, $nb, $support, $id]);
    }
}
?>
