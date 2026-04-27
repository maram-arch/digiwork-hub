<?php
session_start();

require_once __DIR__ . '/../../controller/UserController.php';

// No front-only gate: backoffice login can be used to authenticate directly.

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $currentUser = (new UserController())->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) {
        $currentUser = null;
    }
}

if (!empty($currentUser) && ($currentUser['role'] ?? '') === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller = new UserController();
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error = 'Email et mot de passe obligatoires.';
        } else {
            $result = $controller->login(['email' => $email, 'password' => $password]);

            if ($result['success']) {
                if ($result['user']['role'] !== 'admin') {
                    $error = 'Acces refusé. Seuls les admins peuvent accedre au dashboard.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $result['user']['id'];
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = $result['message'] ?? 'Echec de la connexion.';
            }
        }
    } catch (Throwable $e) {
        $error = 'Service temporairement indisponible.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - DigiWork Hub Dashboard Admin</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #060;
            border: 1px solid #cfc;
        }
        .logo-text {
            font-size: 24px;
            color: #00A651;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-text">DigiWork Hub</div>
            <h1>Dashboard Admin</h1>
            <p>Connectez-vous pour acceder au tableau de bord administrateur</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Connexion Admin</button>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 13px;">
            Admin uniquement • Acces restreint
        </p>
    </div>
</body>
</html>
