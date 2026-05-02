<?php
require_once __DIR__ . '/../../model/Publication.php';
require_once __DIR__ . '/../../model/Commentaire.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) die("ID invalide");
$pub = Publication::getByIdWithUser($id);
if (!$pub) die("Publication introuvable");
$commentaires = Commentaire::getByPublication($id);

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>'.htmlspecialchars($pub['titre']).' - DigiWork Hub</title>
<style>body{font-family:Arial;margin:2cm;} .header{text-align:center;border-bottom:2px solid #435ebe;} .footer{position:fixed;bottom:0;text-align:center;font-size:12px;}</style>
</head><body>
<div class="header"><h1>DigiWork Hub</h1><p>Généré le '.date('d/m/Y H:i').'</p></div>
<h2>'.htmlspecialchars($pub['titre']).'</h2>
<p class="meta">Par '.htmlspecialchars($pub['prenom'].' '.$pub['nom']).' – le '.date('d/m/Y H:i', strtotime($pub['date_publication'])).'</p>
<div class="content">'.nl2br(htmlspecialchars($pub['contenu'])).'</div>
<h3>Commentaires</h3>';
foreach($commentaires as $c) {
    $html .= '<div><strong>'.htmlspecialchars($c['prenom'].' '.$c['nom']).'</strong> – '.date('d/m/Y H:i', strtotime($c['date_commentaire'])).'<p>'.nl2br(htmlspecialchars($c['contenu'])).'</p></div>';
}
$html .= '<div class="footer">DigiWork Hub – Tous droits réservés</div></body></html>';

// Forcer le téléchargement
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="publication_'.$id.'.html"');
echo $html;
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pub['titre']) ?> - DigiWork Hub</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2cm; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #435ebe; padding-bottom: 10px; }
        .header h1 { color: #435ebe; margin: 0; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
        .content { margin: 20px 0; line-height: 1.5; }
        .comment { border-left: 3px solid #435ebe; padding-left: 15px; margin: 15px 0; }
        .meta { color: #666; font-size: 0.9em; margin: 10px 0; }
        img { max-width: 100%; height: auto; }
    </style>
</head>

<body>
    <div class="header">
        <h1>DigiWork Hub</h1>
        <p>Document généré le <?= date('d/m/Y à H:i') ?></p>
    </div>
    <h2><?= htmlspecialchars($pub['titre']) ?></h2>
    <div class="meta">Par <?= htmlspecialchars($pub['prenom'] . ' ' . $pub['nom']) ?> – publié le <?= date('d/m/Y H:i', strtotime($pub['date_publication'])) ?></div>
    <?php if ($pub['image']): ?><img src="../../<?= htmlspecialchars($pub['image']) ?>" style="max-width:100%;"><?php endif; ?>
    <div class="content"><?= nl2br(htmlspecialchars($pub['contenu'])) ?></div>
    <h3>Commentaires (<?= count($commentaires) ?>)</h3>
    <?php foreach ($commentaires as $c): ?>
        <div class="comment">
            <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong> – <small><?= date('d/m/Y H:i', strtotime($c['date_commentaire'])) ?></small>
            <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
        </div>
    <?php endforeach; ?>
    <div class="footer">DigiWork Hub – Tous droits réservés</div>
</body>
</html>