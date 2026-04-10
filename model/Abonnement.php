<?php
require_once("../config/config.php");

class Abonnement {

    function subscribe($user, $pack) {
        global $pdo;

        $sql1 = "INSERT INTO `abonnement`
        (`id-user`, `date-deb`, `date-fin`, `status`)
        VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'actif')";

        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$user]);

        $id_abonnement = $pdo->lastInsertId();

        $sql2 = "INSERT INTO `abon-pack`
        (`id-pack`, `id-abonnement`)
        VALUES (?, ?)";

        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$pack, $id_abonnement]);
    }
}
?>
