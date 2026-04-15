<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once(__DIR__ . '/../../config/config.php');

$users = [];
try {
    // Fetch all user rows and normalize them so templates can rely on 'id_user','nom','email','tel'
    $stmt = $pdo->query("SELECT * FROM `user`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $id = $r['id_user'] ?? $r['id-user'] ?? $r['id'] ?? $r['user_id'] ?? null;
        $name = $r['nom'] ?? $r['name'] ?? $r['fullname'] ?? $r['full_name'] ?? $r['prenom'] ?? '';
        $email = $r['email'] ?? '';
        $tel = $r['tel'] ?? $r['telephone'] ?? $r['phone'] ?? '';
        if ($id) {
            $users[] = ['id_user' => $id, 'nom' => $name, 'email' => $email, 'tel' => $tel];
        }
    }
} catch (PDOException $e) {
    // If the user table doesn't exist or columns differ, return an empty list so the page renders without crashing.
    error_log("Login page: failed to read users - " . $e->getMessage());
    $users = [];
}
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
        body { font-family: 'Inter', sans-serif; background: #F3F4F6; min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .login-container { background:white; border-radius:20px; padding:36px; width:100%; max-width:480px; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
        .login-logo { text-align:center; margin-bottom:20px; }
        .login-logo h2 { font-size:24px; color:#10B981; }
        .form-group { margin-bottom:18px; }
        label { display:block; font-weight:600; margin-bottom:8px; color:#111827 }
        select, button { width:100%; padding:12px 14px; border:1px solid #E5E7EB; border-radius:10px; font-size:15px; }
        button { background:#10B981; color:#fff; border:none; font-weight:600; cursor:pointer; }
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
                <label>Sélectionner un utilisateur</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Choisissez votre compte --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id_user']); ?>"><?php echo htmlspecialchars($user['nom'] ?: ($user['email'] ?: 'Utilisateur')); ?><?php echo $user['email'] ? ' - ' . htmlspecialchars($user['email']) : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Se connecter →</button>
        </form>

        <div style="text-align:center;margin-top:14px;"><a href="index.php" style="color:#6B7280;text-decoration:none;">← Retour à l'accueil</a></div>
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
