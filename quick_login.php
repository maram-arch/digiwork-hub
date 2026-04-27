<?php
session_start();

// Quick login for testing subscription functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    require_once('config/config.php');
    
    $stmt = $pdo->prepare("SELECT id_user, email, tel FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['email'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_tel'] = $user['tel'];
        $_SESSION['role'] = ($email === 'admin@gmail.com') ? 'admin' : 'user';
        
        header('Location: /view/front/abonnement.php');
        exit;
    } else {
        $error = "User not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Login - DigiWork HUB</title>
    <style>
        body { font-family: Arial, sans-serif; background: #FFFFFF; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 16px; border: 1px solid #dddddd; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #00A651; font-weight: 600; }
        input, select { width: 100%; padding: 8px; border: 1px solid #dddddd; border-radius: 10px; }
        button { background: linear-gradient(135deg, #00A651 0%, #008040 100%); color: white; padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .error { color: #dc3545; margin-bottom: 10px; }
    </style>
</head>
<body style="background-color: #FFFFFF !important;">
    <div class="container">
        <h2>Quick Login for Testing</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Select User:</label>
                <select name="email" required>
                    <option value="">Choose a user...</option>
                    <option value="admin@gmail.com">Admin (admin@gmail.com)</option>
                    <option value="jane.smith@email.com">Jane Smith (jane.smith@email.com)</option>
                    <option value="bob.wilson@email.com">Bob Wilson (bob.wilson@email.com)</option>
                </select>
            </div>
            <button type="submit">Login & Test Subscriptions</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            This will log you in and redirect to the subscription page where you can test the "S'abonner" functionality.
        </p>
    </div>
</body>
</html>
