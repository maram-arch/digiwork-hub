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
            <img src="../frontoffice/assets/img/logo/digiwork-hub.png" alt="DigiWork HUB" style="height:52px;">
        </div>
        <div class="nav-links">
            <a href="../frontoffice/index.php">Home</a>
            <a href="packs.php">Packs</a>
            <a href="abonnement.php">Abonnement</a>
            <a href="abonnement.php">Profile</a>
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
    <?php
    // Ensure recommended packs exist and fetch their real IDs
    require_once(__DIR__ . '/../../model/Pack.php');
    $pm = new Pack();

    $recommended_defs = [
        [
            'name' => 'PACK BASIC (Débutant)',
            'prix' => 0.0,
            'duree' => '30 jours',
            'description' => "🎯 Cible\n\n👉 Étudiants / débutants / nouveaux freelances\n\n📦 Contenu\nAccès limité à la plateforme\nNombre de projets max : 2–3\nPas de support prioritaire\nPas d’outils avancés",
            'nb' => 3,
            'support' => 'non'
        ],
        [
            'name' => 'PACK PRO (Standard)',
            'prix' => 35.0,
            'duree' => '1 mois',
            'description' => "🎯 Cible\n\n👉 Freelances actifs / entrepreneurs en croissance\n\n📦 Contenu\nAccès complet aux fonctionnalités\nNombre de projets moyen (10–20)\nSupport normal\nAccès aux recommandations",
            'nb' => 15,
            'support' => 'oui'
        ],
        [
            'name' => 'PACK PREMIUM (Avancé)',
            'prix' => 80.0,
            'duree' => '1 mois',
            'description' => "🎯 Cible\n\n👉 Freelances professionnels / agences / power users\n\n📦 Contenu\nProjets illimités\nSupport prioritaire\nMise en avant du profil\nAccès aux analytics avancés\nAccès aux meilleures opportunités",
            'nb' => 9999,
            'support' => 'oui'
        ]
    ];

    $recommended = [];
    foreach ($recommended_defs as $def) {
        $found = $pm->getByName($def['name']);
        if (!$found) {
            // create pack
            // DB expects a date for `duree` column; convert human-friendly duration to a date string
            $duree_str = $def['duree'];
            $duree_db = date('Y-m-d');
            if (preg_match('/(\d+)\s*jours?/i', $duree_str, $m)) {
                $duree_db = date('Y-m-d', strtotime('+' . intval($m[1]) . ' days'));
            } elseif (preg_match('/(\d+)\s*mois?/i', $duree_str, $m)) {
                $duree_db = date('Y-m-d', strtotime('+' . intval($m[1]) . ' months'));
            } elseif (stripos($duree_str, 'mois') !== false) {
                $duree_db = date('Y-m-d', strtotime('+1 month'));
            } elseif (stripos($duree_str, 'jour') !== false) {
                $duree_db = date('Y-m-d', strtotime('+30 days'));
            }

            $pm->add($def['name'], $def['prix'], $duree_db, $def['description'], $def['nb'], $def['support']);
            $found = $pm->getByName($def['name']);
        }
        if ($found) $recommended[] = $found;
    }
    ?>

    <div style="max-width:1100px;margin:0 auto 24px auto;padding:0 20px;display:flex;gap:20px;">
        <?php foreach ($recommended as $r): ?>
            <div class="pack-card" style="width:33%;">
                <div class="pack-image"><?= htmlspecialchars($r['nom-pack']) ?></div>
                <div class="pack-content">
                    <h3 class="pack-title"><?= htmlspecialchars($r['nom-pack']) ?></h3>
                    <div class="pack-desc"><?= htmlspecialchars($r['description']) ?></div>
                </div>
                <div class="pack-footer">
                    <div class="pack-price-badge"><?= htmlspecialchars($r['prix']) ?> DT</div>
                    <form onsubmit="return subscribeForm(event, <?= htmlspecialchars($r['id-pack']) ?>)">
                        <button type="submit" class="pack-cta">S'abonner</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
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
                                <form onsubmit="return subscribeForm(event, ${pack['id-pack']})">
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
            // Tell server we want a JSON response
            fd.append('ajax', '1');

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
