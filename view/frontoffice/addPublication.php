<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Publication</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/LineIcons.2.0.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        body { background: #F3F3F3; font-family: 'Heebo', sans-serif; }
        .page-header { background: #fff; padding: 18px 28px; border-bottom: 1px solid #e8e8e8; margin-bottom: 28px; }
        .page-header h1 { font-family: 'Fira Sans', sans-serif; font-size: 22px; color: #323450; }
        .back-link { color: #2F80ED; font-size: 20px; text-decoration: none; margin-right: 8px; }
        .back-link:hover { text-decoration: none; }
        .wrapper { padding: 0 28px 40px; }
        .form-card { background: #fff; border-radius: 10px; max-width: 600px; padding: 35px 40px; box-shadow: 0px 5px 25px rgba(218,211,211,0.3); }
        .form-card label { display: block; font-weight: 600; color: #323450; font-size: 14px; margin-bottom: 7px; }
        .form-card input[type=text] { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; font-family: 'Heebo', sans-serif; color: #585978; box-shadow: 0px 2px 8px rgba(218,211,211,0.2); }
        .form-card input[type=text]:focus { border-color: #2F80ED; outline: none; }
        .form-card textarea { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; font-family: 'Heebo', sans-serif; color: #585978; box-shadow: 0px 2px 8px rgba(218,211,211,0.2); resize: vertical; }
        .form-card textarea:focus { border-color: #2F80ED; outline: none; }
        .btn-submit { background: #2F80ED; color: #fff; border: none; padding: 11px 28px; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; font-family: 'Heebo', sans-serif; }
        .btn-submit:hover { background: #1a6fd4; }
        .btn-cancel { color: #585978; font-size: 14px; text-decoration: none; margin-left: 16px; }
        .btn-cancel:hover { color: #2F80ED; text-decoration: none; }
        .error-msg { background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.3); color: #dc3545; padding: 10px 16px; border-radius: 5px; margin-bottom: 18px; font-size: 13px; }
    </style>
</head>
<body>

<div class="page-header">
    <h1>
        <a href="index.php?action=list" class="back-link">&#8592;</a>
        Nouvelle Publication
    </h1>
</div>

<div class="wrapper">
    <div class="form-card">

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php?action=add" method="POST">

            <label>Titre :</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($titre); ?>">
            <br><br>

            <label>Contenu :</label>
            <textarea name="contenu" rows="6"><?php echo htmlspecialchars($contenu); ?></textarea>
            <br><br>

            <button type="submit" class="btn-submit">Ajouter</button>
            <a href="index.php?action=list" class="btn-cancel">Annuler</a>

        </form>
    </div>
</div>

</body>
</html>