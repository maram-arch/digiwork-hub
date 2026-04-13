<?php
require_once(__DIR__ . '/../../config/config.php');
session_start();
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
if ($flash) unset($_SESSION['flash']);

// load users to populate select
$users = [];
try {
    $stmt = $pdo->query('SELECT id_user, nom, tel FROM `user` ORDER BY nom');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Se connecter - DigiWork Hub</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="front-navbar">
        <div class="logo-container">
            <img src="../frontoffice/assets/img/logo/digiwork-hub.png" alt="DigiWork HUB" style="height:48px;">
        </div>
        <div class="nav-links">
            <a href="packs.php">Packs</a>
        </div>
    </div>

    <div style="max-width:500px;margin:60px auto;background:white;padding:24px;border-radius:8px;">
        <h2>Connexion</h2>
        <?php if ($flash): ?>
            <div style="background:#FDE68A;padding:10px;border-radius:6px;margin-bottom:12px;"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <form method="POST" action="../../controller/AuthController.php">
            <input type="hidden" name="action" value="login">
            <div style="margin-bottom:12px;">
                <label>Choisissez un utilisateur (demo)</label>
                <select name="user_id" required style="width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['nom']) ?> (<?= htmlspecialchars($u['tel']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="text-align:right;">
                <button type="submit" style="background:var(--accent);color:white;padding:10px 16px;border-radius:6px;border:none;">Se connecter</button>
            </div>
        </form>
    </div>
</body>
</html>
