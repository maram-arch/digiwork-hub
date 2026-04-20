<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once(__DIR__ . '/../../config/config.php');

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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F9FAFB; min-height: 100vh; display:flex; align-items:center; justify-content:center; padding: 24px; }
        .login-container { background:white; border-radius:20px; padding:36px; width:100%; max-width:480px; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
        .login-logo { text-align:center; margin-bottom:20px; }
        .login-logo h2 { font-size:24px; color:#0A2540; letter-spacing: -0.3px; }
        .form-group { margin-bottom:18px; }
        label { display:block; font-weight:600; margin-bottom:8px; color:#111827 }
        input, button { width:100%; padding:12px 14px; border:1px solid #E5E7EB; border-radius:12px; font-size:15px; }
        input { background: #FFFFFF; }
        button { background:#0A2540; color:#fff; border:none; font-weight:800; cursor:pointer; }
        button:hover { filter: brightness(1.05); }
        .alert { background:#FEF3C7; color:#92400E; padding:12px; border-radius:10px; margin-bottom:16px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div style="font-size:40px;">🌿</div>
            <h2>DigiWork <span style="color:#059669">HUB</span></h2>
            <p style="color:#6B7280;margin-top:8px;">Connectez-vous à votre espace</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="../../controller/AuthController.php" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" placeholder="vous@exemple.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Se connecter →</button>
        </form>

        <div style="text-align:center;margin-top:14px;"><a href="index.php" style="color:#6B7280;text-decoration:none;">← Retour à l'accueil</a></div>
    </div>

    <script></script>
</body>
</html>
