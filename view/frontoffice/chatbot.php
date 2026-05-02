<?php
require_once __DIR__ . '/../../model/Chatbot.php';

$response = "";

if (isset($_POST['message'])) {
    $response = Chatbot::ask($_POST['message']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chatbot</title>
</head>
<body>

<h2>🤖 Chatbot DigiWork</h2>

<form method="POST">
    <input type="text" name="message" placeholder="Pose ta question..." required>
    <button type="submit">Envoyer</button>
</form>

<?php if ($response): ?>
    <p><strong>Réponse :</strong> <?= htmlspecialchars($response) ?></p>
<?php endif; ?>

</body>
</html>