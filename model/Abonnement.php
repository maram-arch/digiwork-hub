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
        // We can't assume the `user` table column names across all installs.
        // Strategy: fetch abonnements + pack info, then resolve the user row per-abonnement
        // by trying multiple common ID column names and mapping user fields to a stable
        // shape (nom, tel) so views/controllers keep working.
        try {
            $sql = "SELECT a.*, p.`nom-pack` FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }

        // helper to map user fields to canonical keys
        $mapUserFields = function($user) {
            $out = ['nom' => '', 'tel' => ''];
            if (!$user || !is_array($user)) return $out;
            // name variants - use email as name since no name columns exist
            foreach (['nom', 'name', 'fullname', 'full_name', 'prenom', 'email'] as $k) {
                if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                    $out['nom'] = $user[$k];
                    break;
                }
            }
            // phone variants
            foreach (['tel', 'telephone', 'phone', 'mobile'] as $k) {
                if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                    $out['tel'] = $user[$k];
                    break;
                }
            }
            return $out;
        };

        // try to fetch a user row given an id value by trying common id column names
        $fetchUserByIdValue = function($idValue) use ($pdo) {
            if ($idValue === null || $idValue === '') return null;
            $candidates = ['id-user','id_user','id','user_id','uid'];
            foreach ($candidates as $col) {
                try {
                    $sql = "SELECT * FROM `user` WHERE `" . $col . "` = ? LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idValue]);
                    $u = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($u) return $u;
                } catch (\PDOException $e) {
                    // column doesn't exist or query failed - try the next candidate
                    continue;
                }
            }
            return null;
        };

        $out = [];
        foreach ($rows as $r) {
            // find the user id field in the abonnement row
            $userId = null;
            foreach (['id-user','id_user','id','user_id','uid'] as $k) {
                if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                    $userId = $r[$k];
                    break;
                }
            }

            $user = $fetchUserByIdValue($userId);
            $mapped = $mapUserFields($user);
            // attach normalized fields so existing views (expecting 'nom' and 'tel') work
            $r['nom'] = $mapped['nom'];
            $r['tel'] = $mapped['tel'];

            $out[] = $r;
        }

        return $out;
    }

    function getByUser($userId) {
        global $pdo;
        // Try multiple likely column names for the user id on the abonnement table.
        $candidates = ['id-user','id_user','id','user_id','uid'];
        foreach ($candidates as $col) {
            try {
                $sql = "SELECT a.*, p.`nom-pack` FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                            WHERE a.`" . $col . "` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // normalize user fields per row using same mapping as in getAllAbonnements
                $mapUserFields = function($user) {
                    $out = ['nom' => '', 'tel' => ''];
                    if (!$user || !is_array($user)) return $out;
                    // name variants - use email as name since no name columns exist
                    foreach (['nom', 'name', 'fullname', 'full_name', 'prenom', 'email'] as $k) {
                        if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                            $out['nom'] = $user[$k];
                            break;
                        }
                    }
                    foreach (['tel', 'telephone', 'phone', 'mobile'] as $k) {
                        if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                            $out['tel'] = $user[$k];
                            break;
                        }
                    }
                    return $out;
                };

                // helper to fetch user row by id value
                $fetchUserByIdValue = function($idValue) use ($pdo) {
                    if ($idValue === null || $idValue === '') return null;
                    $candidates = ['id-user','id_user','id','user_id','uid'];
                    foreach ($candidates as $c) {
                        try {
                            $sql = "SELECT * FROM `user` WHERE `" . $c . "` = ? LIMIT 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$idValue]);
                            $u = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($u) return $u;
                        } catch (\PDOException $e) {
                            continue;
                        }
                    }
                    return null;
                };

                $out = [];
                foreach ($rows as $r) {
                    $userIdDetected = null;
                    foreach (['id-user','id_user','id','user_id','uid'] as $k) {
                        if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                            $userIdDetected = $r[$k];
                            break;
                        }
                    }
                    $user = $fetchUserByIdValue($userIdDetected);
                    $mapped = $mapUserFields($user);
                    $r['nom'] = $mapped['nom'];
                    $r['tel'] = $mapped['tel'];
                    $out[] = $r;
                }

                return $out;
            } catch (\PDOException $e) {
                // try next candidate column
                continue;
            }
        }

        // As a last resort, fall back to fetching all and filtering in PHP (safe, but less efficient).
        $all = $this->getAllAbonnements();
        $filtered = array_filter($all, function($r) use ($userId) {
            foreach (['id-user','id_user','id','user_id','uid'] as $k) {
                if (array_key_exists($k, $r) && (string)$r[$k] === (string)$userId) return true;
            }
            return false;
        });
        return array_values($filtered);
    }

    function update($id, $status, $dateFin = null) {
        global $pdo;
        
        $sql = "UPDATE `abonnement` SET `status` = ?";
        $params = [$status];
        
        if ($dateFin) {
            $sql .= ", `date-fin` = ?";
            $params[] = $dateFin;
        }
        
        $sql .= " WHERE `id-abonnement` = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
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
        $candidates = ['id-user','id_user','id','user_id','uid'];
        foreach ($candidates as $col) {
            try {
                $sql = "SELECT a.*, p.`nom-pack`, p.`nb-proj-max` FROM `abonnement` a
                            JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
                            JOIN `pack` p ON ap.`id-pack` = p.`id-pack`
                            WHERE a.`" . $col . "` = ? AND a.`status` = 'actif' AND a.`date-fin` >= NOW()";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([(int)$userId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // normalize user fields by attempting to load the user row for each abonnement
                $fetchUserByIdValue = function($idValue) use ($pdo) {
                    if ($idValue === null || $idValue === '') return null;
                    $candidates = ['id-user','id_user','id','user_id','uid'];
                    foreach ($candidates as $c) {
                        try {
                            $sql = "SELECT * FROM `user` WHERE `" . $c . "` = ? LIMIT 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$idValue]);
                            $u = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($u) return $u;
                        } catch (\PDOException $e) {
                            continue;
                        }
                    }
                    return null;
                };

                $mapUserFields = function($user) {
                    $out = ['nom' => '', 'tel' => ''];
                    if (!$user || !is_array($user)) return $out;
                    // name variants - use email as name since no name columns exist
                    foreach (['nom', 'name', 'fullname', 'full_name', 'prenom', 'email'] as $k) {
                        if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                            $out['nom'] = $user[$k];
                            break;
                        }
                    }
                    foreach (['tel', 'telephone', 'phone', 'mobile'] as $k) {
                        if (array_key_exists($k, $user) && $user[$k] !== null && $user[$k] !== '') {
                            $out['tel'] = $user[$k];
                            break;
                        }
                    }
                    return $out;
                };

                $out = [];
                foreach ($rows as $r) {
                    $userIdDetected = null;
                    foreach (['id-user','id_user','id','user_id','uid'] as $k) {
                        if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                            $userIdDetected = $r[$k];
                            break;
                        }
                    }
                    $user = $fetchUserByIdValue($userIdDetected);
                    $mapped = $mapUserFields($user);
                    $r['nom'] = $mapped['nom'];
                    $r['tel'] = $mapped['tel'];
                    $out[] = $r;
                }

                return $out;
            } catch (\PDOException $e) {
                continue;
            }
        }

        return [];
    }
}
?>
