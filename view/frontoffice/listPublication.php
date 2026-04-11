<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Publications</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/LineIcons.2.0.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        body { background: #F3F3F3; font-family: 'Heebo', sans-serif; }
        .page-header { background: #fff; padding: 18px 28px; border-bottom: 1px solid #e8e8e8; margin-bottom: 28px; }
        .page-header h1 { font-family: 'Fira Sans', sans-serif; font-size: 22px; color: #323450; display: inline; }
        .badge-total { background: rgba(47,128,237,0.1); color: #2F80ED; font-size: 12px; font-weight: 600; padding: 3px 12px; border-radius: 20px; margin-left: 12px; }
        .btn-new { background: #2F80ED; color: #fff; border: none; padding: 9px 20px; border-radius: 5px; font-size: 14px; font-weight: 500; text-decoration: none; float: right; margin-top: -4px; }
        .btn-new:hover { background: #1a6fd4; color: #fff; text-decoration: none; }
        .wrapper { padding: 0 28px 40px; }
        .pub-card { background: #fff; border-radius: 10px; border-left: 4px solid #2F80ED; padding: 18px 22px; margin-bottom: 14px; box-shadow: 0px 3px 15px rgba(218,211,211,0.25); }
        .pub-card:hover { box-shadow: 0px 5px 20px rgba(47,128,237,0.15); }
        .pub-card-title { font-family: 'Fira Sans', sans-serif; font-size: 16px; font-weight: 600; color: #323450; margin-bottom: 6px; }
        .pub-card-meta { font-size: 12px; color: #585978; margin-bottom: 8px; }
        .pub-card-content { font-size: 13px; color: #585978; line-height: 1.6; margin-bottom: 12px; }
        .btn-edit { font-size: 13px; padding: 5px 14px; border-radius: 5px; background: transparent; color: #2F80ED; border: 1px solid #2F80ED; text-decoration: none; }
        .btn-edit:hover { background: #2F80ED; color: #fff; text-decoration: none; }
        .btn-delete { font-size: 13px; padding: 5px 14px; border-radius: 5px; background: transparent; color: #dc3545; border: 1px solid #dc3545; text-decoration: none; }
        .btn-delete:hover { background: #dc3545; color: #fff; text-decoration: none; }
        .empty-state { text-align: center; padding: 60px 20px; color: #585978; }
    </style>
</head>
<body>

<div class="page-header">
    <h1>Publications du Forum</h1>
    <span class="badge-total"><?php echo $total; ?> publication(s)</span>
    <a href="index.php?action=add" class="btn-new">+ Nouvelle publication</a>
    <div style="clear:both;"></div>
</div>

<div class="wrapper">
    <?php if (empty($publications)): ?>
        <div class="empty-state">
            <p>Aucune publication pour le moment.</p>
            <br>
            <a href="index.php?action=add" class="btn-new">+ Créer une publication</a>
        </div>
    <?php else: ?>
        <?php foreach ($publications as $pub): ?>
            <div class="pub-card">
                <div class="pub-card-title"><?php echo htmlspecialchars($pub['titre']); ?></div>
                <div class="pub-card-meta">
                    Date : <?php echo htmlspecialchars($pub['date_publication']); ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    Utilisateur : #<?php echo htmlspecialchars($pub['id_user']); ?>
                </div>
                <div class="pub-card-content">
                    <?php echo htmlspecialchars(substr($pub['contenu'], 0, 150)); ?>...
                </div>
                <a href="index.php?action=edit&id=<?php echo $pub['id_publication']; ?>" class="btn-edit">Modifier</a>
                &nbsp;
                <a href="index.php?action=delete&id=<?php echo $pub['id_publication']; ?>"
                   class="btn-delete"
                   onclick="return confirm('Supprimer cette publication ?');">Supprimer</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>