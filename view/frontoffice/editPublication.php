<?php
$id_user_session = $_SESSION['id_user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Publication — DigiWork Hub</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/LineIcons.2.0.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        :root {
            --primary: #2F80ED;
            --primary-dark: #1a6fd4;
            --danger: #EF4444;
            --bg: #F0F4F8;
            --border: #E2E8F0;
            --text: #1E293B;
            --muted: #64748B;
        }
        * { box-sizing: border-box; }
        body { background: var(--bg); font-family: 'Heebo', sans-serif; margin: 0; }

        .page-header { background: #fff; padding: 16px 32px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
        .back-btn { color: var(--muted); font-size: 20px; text-decoration: none; }
        .back-btn:hover { color: var(--primary); }
        .page-header h1 { font-family: 'Fira Sans', sans-serif; font-size: 20px; font-weight: 700; color: var(--text); margin: 0; }
        .pub-id { background: rgba(47,128,237,0.1); color: var(--primary); font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }

        .wrapper { padding: 32px; display: flex; justify-content: center; }
        .form-card { background: #fff; border-radius: 14px; width: 100%; max-width: 680px; padding: 36px 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.07); border-top: 4px solid var(--primary); }

        .error-msg { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); color: #7F1D1D; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; line-height: 1.6; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; color: var(--text); font-size: 14px; margin-bottom: 6px; }
        .form-group label span.req { color: var(--danger); margin-left: 2px; }
        .form-group input[type=text],
        .form-group input[type=date],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; font-family: 'Heebo', sans-serif;
            color: var(--text); background: #fff;
            transition: border-color .2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(47,128,237,0.1); }
        .char-count { font-size: 11px; color: var(--muted); text-align: right; margin-top: 4px; }
        .char-count.warn { color: var(--danger); }

        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; }
        .cat-radio { display: none; }
        .cat-label {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            padding: 10px 8px; border: 2px solid var(--border); border-radius: 10px;
            cursor: pointer; font-size: 12px; font-weight: 600; color: var(--muted);
            text-align: center; transition: all .2s; background: #fff;
        }
        .cat-label:hover { border-color: var(--primary); color: var(--primary); }
        .cat-radio:checked + .cat-label { border-color: var(--primary); background: rgba(47,128,237,0.07); color: var(--primary); }
        .cat-icon { font-size: 22px; }

        .textarea-wrap { position: relative; }
        .emoji-toolbar {
            display: flex; gap: 4px; flex-wrap: wrap;
            padding: 8px 10px; background: #F8FAFC;
            border: 1.5px solid var(--border); border-bottom: none;
            border-radius: 8px 8px 0 0;
        }
        .emoji-btn { background: none; border: none; font-size: 18px; cursor: pointer; padding: 2px 4px; border-radius: 4px; transition: background .15s; line-height: 1; }
        .emoji-btn:hover { background: #E2E8F0; }
        .emoji-toolbar + textarea { border-radius: 0 0 8px 8px; }

        .event-section {
            background: rgba(239,68,68,0.05); border: 1.5px solid rgba(239,68,68,0.2);
            border-radius: 10px; padding: 18px 20px; margin-top: 8px; display: none;
        }
        .event-section.show { display: block; }
        .toggle-event-label { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
        .toggle-event-label input[type=checkbox] { width: 16px; height: 16px; accent-color: #EF4444; }
        .toggle-event-label span { font-weight: 600; font-size: 14px; color: var(--text); }

        /* Image actuelle */
        .current-img-box { margin-bottom: 12px; }
        .current-img-box img { max-height: 140px; border-radius: 8px; border: 1px solid var(--border); }
        .current-img-box p { font-size: 12px; color: var(--muted); margin: 6px 0 0; }

        .upload-zone {
            border: 2px dashed var(--border); border-radius: 10px;
            padding: 20px; text-align: center; cursor: pointer;
            transition: border-color .2s; background: #F8FAFC;
        }
        .upload-zone:hover { border-color: var(--primary); }
        .upload-zone input[type=file] { display: none; }
        .upload-zone p { font-size: 13px; color: var(--muted); margin: 0; }
        .upload-zone p strong { color: var(--primary); }
        .img-preview { margin-top: 12px; display: none; }
        .img-preview img { max-height: 140px; border-radius: 8px; border: 1px solid var(--border); }

        .form-actions { display: flex; align-items: center; gap: 16px; margin-top: 8px; }
        .btn-submit { background: var(--primary); color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; font-family: 'Heebo', sans-serif; transition: background .2s; }
        .btn-submit:hover { background: var(--primary-dark); }
        .btn-cancel { color: var(--muted); font-size: 14px; text-decoration: none; }
        .btn-cancel:hover { color: var(--primary); }

        @media(max-width:600px) { .form-card { padding: 24px 18px; } .wrapper { padding: 16px; } }
    </style>
</head>
<body>

<div class="page-header">
    <a href="index.php?action=list" class="back-btn">&#8592;</a>
    <h1>Modifier la Publication</h1>
    <span class="pub-id">#<?php echo (int)$publication['id_publication']; ?></span>
</div>

<div class="wrapper">
    <div class="form-card">

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="index.php?action=edit&id=<?php echo (int)$publication['id_publication']; ?>"
              method="POST" enctype="multipart/form-data" id="editForm">

            <!-- Titre -->
            <div class="form-group">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" id="titreInput"
                       value="<?php echo htmlspecialchars($publication['titre']); ?>"
                       maxlength="100">
                <div class="char-count" id="titreCount">0 / 100</div>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
                <label>Catégorie <span class="req">*</span></label>
                <div class="cat-grid">
                    <?php
                    $cats = [
                        'general'   => ['icon'=>'💬','label'=>'Général'],
                        'stage'     => ['icon'=>'🎓','label'=>'Stage'],
                        'job'       => ['icon'=>'💼','label'=>'Emploi'],
                        'question'  => ['icon'=>'❓','label'=>'Question'],
                        'evenement' => ['icon'=>'📅','label'=>'Événement'],
                    ];
                    $cur_cat = $publication['categorie'] ?? 'general';
                    foreach ($cats as $val => $c):
                    ?>
                    <div>
                        <input type="radio" name="categorie" id="cat_<?php echo $val; ?>"
                               value="<?php echo $val; ?>" class="cat-radio"
                               <?php echo $cur_cat === $val ? 'checked' : ''; ?>>
                        <label for="cat_<?php echo $val; ?>" class="cat-label">
                            <span class="cat-icon"><?php echo $c['icon']; ?></span>
                            <?php echo $c['label']; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contenu avec émojis -->
            <div class="form-group">
                <label>Contenu <span class="req">*</span></label>
                <div class="textarea-wrap">
                    <div class="emoji-toolbar">
                        <?php
                        $emojis = ['😀','😂','🥰','😎','🤔','👍','👏','🔥','💡','⭐','🎉','🚀','✅','❌','⚠️','💼','🎓','📅','📍','🙏','💪','😅','🤝','👋','💬'];
                        foreach ($emojis as $em): ?>
                            <button type="button" class="emoji-btn" onclick="insertEmoji('<?php echo $em; ?>')"><?php echo $em; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="contenu" id="contenuTextarea" rows="7"><?php echo htmlspecialchars($publication['contenu']); ?></textarea>
                </div>
            </div>

            <!-- Option Événement -->
            <div class="form-group">
                <label class="toggle-event-label">
                    <input type="checkbox" name="is_event" id="isEventCheck" value="1"
                           <?php echo !empty($publication['is_event']) ? 'checked' : ''; ?>
                           onchange="toggleEventSection()">
                    <span>📅 C'est un événement</span>
                </label>
                <div class="event-section <?php echo !empty($publication['is_event']) ? 'show' : ''; ?>" id="eventSection">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#7F1D1D;margin-bottom:5px;display:block;">📆 Date</label>
                            <input type="date" name="event_date"
                                   value="<?php echo htmlspecialchars($publication['event_date'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#7F1D1D;margin-bottom:5px;display:block;">📍 Lieu</label>
                            <input type="text" name="event_lieu"
                                   value="<?php echo htmlspecialchars($publication['event_lieu'] ?? ''); ?>"
                                   placeholder="Ex: Tunis, Lac 2…">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="form-group">
                <label>🖼️ Image</label>
                <?php if (!empty($publication['image'])): ?>
                    <div class="current-img-box">
                        <img src="../../public/uploads/publications/<?php echo htmlspecialchars($publication['image']); ?>" alt="image actuelle">
                        <p>Image actuelle — en choisir une nouvelle la remplacera</p>
                    </div>
                <?php endif; ?>
                <div class="upload-zone" onclick="document.getElementById('imageInput').click()">
                    <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(this)">
                    <p><strong>Cliquez pour changer l'image</strong></p>
                    <p>JPG, PNG, GIF, WebP — max 3MB</p>
                </div>
                <div class="img-preview" id="imgPreview">
                    <img id="previewImg" src="" alt="preview">
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">💾 Enregistrer</button>
                <a href="index.php?action=list" class="btn-cancel">Annuler</a>
            </div>

        </form>
    </div>
</div>

<script>
const titreInput = document.getElementById('titreInput');
const titreCount = document.getElementById('titreCount');
function updateTitreCount() {
    const len = titreInput.value.length;
    titreCount.textContent = len + ' / 100';
    titreCount.className = 'char-count' + (len > 85 ? ' warn' : '');
}
titreInput.addEventListener('input', updateTitreCount);
updateTitreCount();

function insertEmoji(emoji) {
    const ta = document.getElementById('contenuTextarea');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + emoji + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + emoji.length;
    ta.focus();
}

function toggleEventSection() {
    const check   = document.getElementById('isEventCheck');
    const section = document.getElementById('eventSection');
    section.classList.toggle('show', check.checked);
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imgPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    const titre   = document.getElementById('titreInput').value.trim();
    const contenu = document.getElementById('contenuTextarea').value.trim();
    const msgs = [];
    if (!titre)   msgs.push('Le titre est obligatoire.');
    if (!contenu) msgs.push('Le contenu est obligatoire.');
    const isEvent = document.getElementById('isEventCheck').checked;
    if (isEvent) {
        const d = document.querySelector('[name=event_date]').value;
        if (!d) msgs.push("La date de l'événement est obligatoire.");
    }
    if (msgs.length) {
        e.preventDefault();
        let existing = document.querySelector('.error-msg');
        if (!existing) {
            existing = document.createElement('div');
            existing.className = 'error-msg';
            document.querySelector('.form-card').insertBefore(existing, document.querySelector('form'));
        }
        existing.innerHTML = msgs.join('<br>');
        existing.scrollIntoView({behavior:'smooth',block:'start'});
    }
});
</script>

</body>
</html>