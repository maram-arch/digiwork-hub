<?php
session_start();
if (!isset($_SESSION['id_user'])) $_SESSION['id_user'] = 1;

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publication = (int)($_POST['id_publication'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);

    if ($id_publication <= 0 || strlen($contenu) < 2) {
        die("Erreur : données invalides (id=$id_publication, contenu='$contenu')");
    }

    $pdo = Config::getConnexion();
    $sql = "INSERT INTO commentaire (contenu, date_commentaire, id_publication, id_user, parent_id, nb_likes) 
            VALUES (?, NOW(), ?, ?, ?, 0)";
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute([$contenu, $id_publication, $_SESSION['id_user'], $parent_id])) {
        $errorInfo = $stmt->errorInfo();
        die("Erreur SQL : " . $errorInfo[2]);
    }

    header('Location: detail_publication.php?id=' . $id_publication . '&status=success&msg=OK');
    exit;
}
?>