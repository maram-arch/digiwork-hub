<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Packs - DigiWork Hub</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php
    session_start();
    // show flash if any
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    if ($flash) unset($_SESSION['flash']);
    ?>

    <div class="front-navbar">
        <div class="logo-container">
            <img src="../frontoffice/assets/img/logo/digiwork-hub.png" alt="DigiWork HUB" style="height:48px;">
        </div>
        <div class="nav-links">
            <a href="../frontoffice/index.php">Accueil</a>
            <a href="#">Projets</a>
            <a href="packs.php">Packs & Formations</a>
            <a href="#">Durabilité</a>
            <a href="abonnement.php">Mon Profil</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #FFF; margin-left: 12px; font-weight:600;">Bonjour, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
                <a href="../../controller/AuthController.php?action=logout" style="margin-left:8px;">Se déconnecter</a>
            <?php else: ?>
                <a href="login.php" style="margin-left:8px;">Se connecter</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <h1>Trouvez les meilleures opportunités freelances pour vous !</h1>
        <p>Boostez votre carrière digitale avec nos abonnements prioritaires, gestion de projets et support VIP.</p>
        <div class="hero-buttons">
            <a href="#" class="btn-white">Explorez les Projets</a>
            <a href="#packs-container" class="btn-green">Voir les Packs</a>
        </div>
    </div>

    <h2 class="section-title">Packs Recommandés pour Vous</h2>
    <!-- Recommended static packs (Free, Standard, Premium) -->
    <div style="max-width:1100px;margin:0 auto 24px auto;padding:0 20px;display:flex;gap:20px;">
        <div class="pack-card" style="width:33%;">
            <div class="pack-card-header" style="background:linear-gradient(90deg,#F9FAFB 0,#E6C79C 100%);">
                <div style="font-size:18px;font-weight:700;color:var(--primary);">Free</div>
            </div>
            <div class="pack-content">
                <div>
                    <h3 class="pack-title">Pack Gratuit</h3>
                    <div class="pack-price">0 dt — Essai</div>
                    <p style="font-size:13px;color:var(--text-muted);">Accès limité, parfait pour tester la plateforme.</p>
                    <div style="font-size:13px;color:var(--text-muted);">Projets max: <strong>1</strong> • Support prioritaire: <strong>non</strong></div>
                </div>
                <form method="POST" action="../../controller/AbonnementController.php">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="hidden" name="pack_id" value="0">
                    <button type="submit" class="btn-accent">S'abonner</button>
                </form>
            </div>
        </div>

        <div class="pack-card" style="width:33%;">
            <div class="pack-card-header" style="background:linear-gradient(90deg,#E6C79C 0,#F59E0B 100%);">
                <div style="font-size:18px;font-weight:700;color:white;">Standard</div>
            </div>
            <div class="pack-content">
                <div>
                    <h3 class="pack-title">Pack Standard</h3>
                    <div class="pack-price">49 dt / mois</div>
                    <p style="font-size:13px;color:var(--text-muted);">Pour freelances actifs — gestion de 5 projets et support prioritaire.</p>
                    <div style="font-size:13px;color:var(--text-muted);">Projets max: <strong>5</strong> • Support prioritaire: <strong>oui</strong></div>
                </div>
                <form method="POST" action="../../controller/AbonnementController.php">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="hidden" name="pack_id" value="-1">
                    <button type="submit" class="btn-accent">S'abonner</button>
                </form>
            </div>
        </div>

        <div class="pack-card" style="width:33%;">
            <div class="pack-card-header" style="background:linear-gradient(90deg,#0A2540 0,#174276 100%);">
                <div style="font-size:18px;font-weight:700;color:white;">Premium</div>
            </div>
            <div class="pack-content">
                <div>
                    <h3 class="pack-title">Pack Premium</h3>
                    <div class="pack-price">129 dt / mois</div>
                    <p style="font-size:13px;color:var(--text-muted);">Toutes les fonctionnalités + support VIP et intégrations.</p>
                    <div style="font-size:13px;color:var(--text-muted);">Projets max: <strong>Illimité</strong> • Support prioritaire: <strong>oui</strong></div>
                </div>
                <form method="POST" action="../../controller/AbonnementController.php">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="hidden" name="pack_id" value="-2">
                    <button type="submit" class="btn-accent">S'abonner</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
        <div style="max-width:1100px;margin:20px auto;">
            <div style="background:#D1FAE5;color:#065F46;padding:12px;border-radius:8px;font-weight:bold;"><?= htmlspecialchars($flash) ?></div>
        </div>
    <?php endif; ?>

    <div id="packs-container" class="cards-container">
        <!-- Packs will be injected here via Fetch API -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../controller/PackController.php?action=getAll')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('packs-container');
                let html = '';
                data.forEach(pack => {
                    html += `
                        <div class="pack-card">
                            <div class="pack-card-header">
                                <div style="box-sizing: border-box; background: var(--secondary); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px;">${pack['nom-pack']}</div>
                            </div>
                            <div class="pack-content">
                                <div>
                                    <span class="pack-tag">${pack['nb-proj-max']} Projets Max</span>
                                    <h3 class="pack-title">${pack['nom-pack']}</h3>
                                    <div class="pack-price">${pack.prix} dt / ${pack.duree} j</div>
                                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                                        Support Prioritaire: <strong>${pack['support-prioritaire']}</strong><br><br>
                                        ${pack.description}
                                    </p>
                                </div>
                                <form method="POST" action="../../controller/AbonnementController.php">
                                    <input type="hidden" name="action" value="subscribe">
                                    <input type="hidden" name="pack_id" value="${pack['id-pack']}">
                                    <button type="submit" class="btn-accent">S'abonner</button>
                                </form>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => console.error(err));
        });

        function subscribeForm(e, packId) {
            // Use fetch to submit and stay on page or redirect to profile
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'subscribe');
            fd.append('pack_id', packId);

            fetch('../../controller/AbonnementController.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    alert('Félicitations, abonnement réussi !');
                    window.location.href = 'abonnement.php';
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
