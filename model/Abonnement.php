<?php
require_once(__DIR__ . "/../config/config.php");

class Abonnement {

    function subscribe($user, $pack) {
        global $pdo;

        // Basic casting - consider stronger validation (existence, permissions) if needed
        $user = (int) $user;
        $pack = (int) $pack;

        try {
            $pdo->beginTransaction();

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

            $pdo->commit();

            // Return the created subscription ID
            return $id_abonnement;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Consider logging $e->getMessage() somewhere
            return false;
        }
    }

    function getAllAbonnements() {
        global $pdo;
        return $pdo->query("SELECT a.*, p.`nom-pack`, u.nom, u.tel FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                            JOIN `user` u ON a.`id-user` = u.id_user")->fetchAll(PDO::FETCH_ASSOC);
    }

    function getByUser($userId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT a.*, p.`nom-pack`, u.nom, u.tel FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                            JOIN `user` u ON a.`id-user` = u.id_user
                            WHERE a.`id-user` = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function delete($id) {
        global $pdo;
        // Due to FK constraints, delete from abon-pack first
        $stmt1 = $pdo->prepare("DELETE FROM `abon-pack` WHERE `id-abonnement`=?");
        $stmt1->execute([$id]);
        
        $stmt2 = $pdo->prepare("DELETE FROM `abonnement` WHERE `id-abonnement`=?");
        $stmt2->execute([$id]);
    }

    // Check and update expired subscriptions. Returns number of rows updated.
    function updateExpiredStatus() {
        global $pdo;
        $affected = $pdo->exec("UPDATE `abonnement` SET `status` = 'expiré' WHERE `date-fin` < NOW() AND `status` = 'actif'");
        return $affected;
    }
    
    function getActiveByUser($userId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT a.*, p.`nom-pack`, p.`nb-proj-max` FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                            WHERE a.`id-user` = ? AND a.`status` = 'actif' AND a.`date-fin` >= NOW()");
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
