<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - DigiWork Hub</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .profile-container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .list-item { background: white; padding: 20px; border-radius: 8px; border: 1px solid #E5E7EB; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .list-info h3 { margin: 0 0 10px 0; color: var(--primary); }
    </style>
</head>
<body>
    <?php
    session_start();
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    if ($flash) unset($_SESSION['flash']);
    ?>

    <?php
    $active = 'profile';
    require_once(__DIR__ . '/../partials/front_nav.php');
    ?>

    <?php if ($flash): ?>
        <div style="max-width:900px;margin:20px auto;">
            <div style="background:#D1FAE5;color:#065F46;padding:12px;border-radius:8px;font-weight:bold;"><?= htmlspecialchars($flash) ?></div>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <h2 style="color: var(--primary);">S'abonner à un Pack</h2>

        <div id="packs-list">
            <p style="color: var(--text-muted);">Chargement des packs...</p>
        </div>

        <hr style="margin:30px 0;">

        <h2 style="color: var(--primary);">Mes Abonnements</h2>
        <div id="abonnements-container">
            <p style="color: var(--text-muted);">Chargement de vos abonnements...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Load available packs (reuse PackController)
            fetch('../../controller/PackController.php?action=getAll')
            .then(res => res.json())
            .then(packs => {
                const container = document.getElementById('packs-list');
                if (!packs || packs.length === 0) {
                    container.innerHTML = '<p>Aucun pack disponible pour le moment.</p>';
                    return;
                }

                let html = '';
                packs.forEach(pack => {
                    html += `
                        <div class="list-item" style="align-items:flex-start;">
                            <div style="flex:1">
                                <h3 style="margin:0 0 8px 0;">${pack['nom-pack']}</h3>
                                <div style="font-weight:700;color:var(--green-card);">${pack.prix} dt — ${pack.duree}</div>
                                <p style="margin:8px 0;color:var(--text-muted);">${pack.description}</p>
                                <div style="font-size:13px;color:var(--text-muted);">Projets max: <strong>${pack['nb-proj-max']}</strong> • Support prioritaire: <strong>${pack['support-prioritaire']}</strong></div>
                            </div>
                            <div style="min-width:160px;margin-left:18px;display:flex;align-items:center;">
                                <form onsubmit="return subscribeForm(event, ${pack['id-pack']})">
                                    <button type="submit" class="btn-accent">S'abonner</button>
                                </form>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('packs-list').innerHTML = '<p style="color:red">Erreur de chargement des packs.</p>';
            });

            // Load abonnements (for current user) via AbonnementController
            fetch('../../controller/AbonnementController.php?action=getMine')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('abonnements-container');
                // If user is logged in, server will include user info; client filters by session user id if provided by server
                // For now, we show abonnements and rely on server-side filtering if desired.
                if (!data || data.length === 0) {
                    container.innerHTML = `<p>Vous n'avez aucun abonnement actif. <a href="packs.php">Découvrir nos offres</a></p>`;
                    return;
                }

                // If session user exists, server returns all abonnements; filter by a mocked current user if present via DOM (we can't access PHP session from JS)
                // We'll display all abonnements but mark them with name/phone
                let html = '';
                data.forEach(abo => {
                    html += `
                        <div class="list-item">
                            <div class="list-info">
                                <h3>Pack : ${abo['nom-pack']}</h3>
                                <p style="font-size: 14px; color: var(--text-muted); margin:0;">
                                    <strong>Client :</strong> ${abo.nom} — ${abo.tel} <br>
                                    <strong>Du :</strong> ${abo['date-deb']} <br>
                                    <strong>Au :</strong> ${abo['date-fin']}
                                </p>
                            </div>
                            <div>
                                <span style="background: #D1FAE5; color: #065F46; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">${abo.status}</span>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('abonnements-container').innerHTML = `<p style="color:red">Erreur de chargement des abonnements.</p>`;
            });
        });

        function subscribeForm(e, packId) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'subscribe');
            fd.append('pack_id', packId);
            fd.append('ajax', '1');

            fetch('../../controller/AbonnementController.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert('Abonnement créé avec succès.');
                    window.location.reload();
                } else {
                    alert('Erreur: ' + res.message);
                }
            })
            .catch(err => alert('Erreur réseau.'));

            return false;
        }
    </script>
</body>
</html>
