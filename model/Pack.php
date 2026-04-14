<?php
require_once(__DIR__ . "/../config/config.php");

class Pack {

    function getAll() {
        global $pdo;
        return $pdo->query("SELECT * FROM `pack`");
    }

    function add($nom, $prix, $duree, $desc, $nb, $support) {
        global $pdo;

        // Ensure values fit database column sizes to avoid SQL errors (truncate if necessary)
    // Use conservative safe lengths to avoid 'Data too long' errors.
    // If your DB schema uses different sizes, update these constants accordingly.
    $nom = mb_substr($nom, 0, 30);
    $desc = mb_substr($desc, 0, 500);

        $sql = "INSERT INTO `pack`
        (`nom-pack`, `prix`, `duree`, `description`, `nb-proj-max`, `support-prioritaire`)
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nom, $prix, $duree, $desc, $nb, $support]);
            return true;
        } catch (PDOException $e) {
            // Log and return false to avoid blowing up the front page.
            error_log("Pack::add failed - " . $e->getMessage());
            return false;
        }
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

    function getByName($name) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM `pack` WHERE `nom-pack` = ? LIMIT 1");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function update($id, $nom, $prix, $duree, $desc, $nb, $support) {
        global $pdo;
        // Truncate fields to safe lengths before update
    $nom = mb_substr($nom, 0, 30);
    $desc = mb_substr($desc, 0, 500);

        $sql = "UPDATE `pack` SET `nom-pack`=?, `prix`=?, `duree`=?, `description`=?, `nb-proj-max`=?, `support-prioritaire`=? WHERE `id-pack`=?";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nom, $prix, $duree, $desc, $nb, $support, $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Pack::update failed - " . $e->getMessage());
            return false;
        }
    }
}
?>
