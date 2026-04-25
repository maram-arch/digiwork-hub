<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#F0FDF4;
            --primary:#0A2540;
            --border:#D1FAE5;
            --muted:#64748B;
            --accent:#10B981;
            --danger:#EF4444;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{width:100%;max-width:420px;background:#fff;border:1px solid var(--border);border-radius:18px;box-shadow:0 20px 50px rgba(2,6,23,.08);padding:26px}
        .logo-top{display:flex;justify-content:center;margin-bottom:24px}
        .logo-top img{height:64px;filter:drop-shadow(0 4px 12px rgba(10,37,64,.12))}
        .title{margin:0 0 8px 0;font-size:22px;font-weight:900;color:var(--primary)}
        .subtitle{margin:0 0 18px 0;color:var(--muted);font-size:14px}
        .alert{background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;padding:10px 12px;border-radius:12px;margin-bottom:14px;font-weight:700;font-size:13px}
        .field{margin-bottom:12px}
        label{display:block;margin-bottom:6px;color:var(--primary);font-weight:800;font-size:13px}
        input{width:100%;padding:12px 12px;border:1px solid var(--border);border-radius:12px;font-size:14px}
        input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 4px rgba(16,185,129,.18)}
        button{width:100%;border:none;border-radius:12px;padding:12px 14px;font-weight:900;background:var(--accent);color:#fff;cursor:pointer;transition:filter .2s}
        button:hover{filter:brightness(1.08)}
        .hint{margin-top:12px;text-align:center;font-size:13px;color:var(--muted)}
        .hint a{color:var(--primary);text-decoration:none;font-weight:800}
    </style>
</head>
<body>
    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $flash = $_SESSION['flash'] ?? null;
    if ($flash) unset($_SESSION['flash']);
    ?>
    <div style="display:flex;flex-direction:column;align-items:center;width:100%;max-width:420px;">
        <div class="logo-top">
            <img src="../../assets/img/logo.png" alt="DigiWork HUB">
        </div>
        <div class="card" style="width:100%;">
        <h1 class="title">Connexion</h1>
        <p class="subtitle">Accédez à votre espace DigiWork HUB.</p>

        <?php if ($flash): ?>
            <div class="alert"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>

        <form method="POST" action="../../controller/AuthController.php" autocomplete="on">
            <input type="hidden" name="action" value="login">

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required placeholder="vous@exemple.com">
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" required placeholder="••••••••">
            </div>

            <button type="submit">Se connecter</button>
        </form>

        <div class="hint">
            <a href="../frontoffice/index.php">← Retour au site</a>
        </div>
        </div>
    </div>
</body>
</html>
