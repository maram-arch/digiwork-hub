<?php if ($publication): ?>

<h2><?= htmlspecialchars($publication['titre']) ?></h2>

<p><?= nl2br(htmlspecialchars($publication['contenu'])) ?></p>

<hr>

<h3>Commentaires</h3>

<!-- FORM AJOUT COMMENTAIRE -->
<form method="POST" action="index.php?action=addComment">
    <input type="hidden" name="id_publication" value="<?= $publication['id_publication'] ?>">

    <textarea name="contenu" placeholder="Votre commentaire..." required
        style="width:100%; height:80px; padding:10px; margin-bottom:10px;"></textarea>

    <button type="submit"
        style="padding:10px 20px; background:#007bff; color:white; border:none; cursor:pointer;">
        Ajouter commentaire
    </button>
</form>

<hr>

<!-- LISTE COMMENTAIRES -->
<?php if (!empty($commentaires)): ?>
    
    <?php foreach($commentaires as $c): ?>
        
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; border-radius:8px;">
            
            <p><?= htmlspecialchars($c['contenu']) ?></p>

            <small style="color:gray;">
                <?= $c['date_creation'] ?? '' ?>
            </small>

            <br><br>

            <a href="index.php?action=deleteComment&id=<?= $c['id_commentaire'] ?>&pub=<?= $publication['id_publication'] ?>"
               style="color:red; text-decoration:none;">
                🗑 Supprimer
            </a>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>Aucun commentaire</p>

<?php endif; ?>

<?php else: ?>

    <p>Publication introuvable</p>

<?php endif; ?>