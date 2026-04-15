<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once(__DIR__ . '/../../config/config.php');

$stmt = $pdo->query("SELECT id_user, nom, email FROM user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #F3F4F6; /* light mode */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 32px;
            padding: 48px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo h2 {
            font-size: 28px;
            color: var(--primary, #10B981);
            margin-top: 16px;
        }
        .login-logo span {
            color: #059669;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #1F2937;
        }
        select, button {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s;
        }
        select:focus {
            outline: none;
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        button {
            background: #10B981;
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: #6B7280;
            text-decoration: none;
        }
        .alert {
            background: #FEF3C7;
            color: #92400E;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div style="font-size: 48px;">🌿</div>
            <h2>DigiWork <span>HUB</span></h2>
            <p style="color: #6B7280; margin-top: 8px;">Connectez-vous à votre espace</p>
        </div>
        
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert"><?= htmlspecialchars($_SESSION['flash']) ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <form method="POST" action="../../controller/AuthController.php" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Sélectionner un utilisateur</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Choisissez votre compte --</option>
                    <?php foreach($users as $user): ?>
                        <option value="<?= $user['id_user'] ?>">
                            <?= htmlspecialchars($user['nom']) ?> - <?= htmlspecialchars($user['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Se connecter →</button>
        </form>
        <div class="back-link">
            <a href="index.php">← Retour à l'accueil</a>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!document.getElementById('user_id').value) {
                e.preventDefault();
                alert('Veuillez sélectionner un utilisateur');
            }
        });
    </script>
</body>
</html>
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
            <img src="../frontoffice/assets/img/logo/logo.png" alt="DigiWork HUB" style="height:48px;">
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
