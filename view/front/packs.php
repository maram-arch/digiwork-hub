<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Packs - DigiWork Hub</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background-color: #FFFFFF !important;">
    <?php
    session_start();
    // show flash if any
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    if ($flash) unset($_SESSION['flash']);
    ?>

    <div class="front-navbar">
        <div class="logo-container">
            <img src="../../assets/img/logo.png" alt="DigiWork HUB" style="height:52px;">
        </div>
        <div class="nav-links">
            <a href="../frontoffice/index.php">Home</a>
            <a href="packs.php">Packs</a>
            <a href="abonnement.php">Abonnement</a>
            <a href="abonnement.php">Profile</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #333333; margin-left: 12px; font-weight:600;">Bonjour, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
                <a href="../../controller/AuthController.php?action=logout" style="margin-left:8px;">Se déconnecter</a>
            <?php else: ?>
                <a href="login.php" style="margin-left:8px;">Se connecter</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <h1 style="color: #333333;">Trouvez les meilleures opportunités freelances pour vous !</h1>
        <p style="color: #666666;">Boostez votre carrière digitale avec nos abonnements prioritaires, gestion de projets et support VIP.</p>
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

    <!-- Modal for manual abonnement entry -->
    <div id="abonnementModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h2 style="margin: 0 0 20px 0; color: #333333;">Ajouter un Abonnement</h2>
            <form id="abonnementForm">
                <input type="hidden" id="packId" name="pack_id">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #666666; font-weight: 600;">Nom</label>
                    <input type="text" id="nom" name="nom" required style="width: 100%; padding: 10px; border: 1px solid #dddddd; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #666666; font-weight: 600;">Téléphone</label>
                    <input type="text" id="tel" name="tel" required style="width: 100%; padding: 10px; border: 1px solid #dddddd; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #666666; font-weight: 600;">Date de début</label>
                    <input type="date" id="dateDeb" name="date_deb" required style="width: 100%; padding: 10px; border: 1px solid #dddddd; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #666666; font-weight: 600;">Date de fin</label>
                    <input type="date" id="dateFin" name="date_fin" required style="width: 100%; padding: 10px; border: 1px solid #dddddd; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; color: #666666; font-weight: 600;">Statut</label>
                    <select id="status" name="status" required style="width: 100%; padding: 10px; border: 1px solid #dddddd; border-radius: 8px; font-size: 14px;">
                        <option value="actif">Actif</option>
                        <option value="expiré">Expiré</option>
                        <option value="en_attente">En attente</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" style="padding: 10px 20px; border: 1px solid #dddddd; background: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Annuler</button>
                    <button type="submit" style="padding: 10px 20px; border: none; background: #00A651; color: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Confirmer</button>
                </div>
            </form>
        </div>
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
            e.preventDefault();
            
            // Show modal with pack ID
            document.getElementById('packId').value = packId;
            document.getElementById('abonnementModal').style.display = 'flex';
            
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const nextMonth = new Date();
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            const nextMonthStr = nextMonth.toISOString().split('T')[0];
            
            document.getElementById('dateDeb').value = today;
            document.getElementById('dateFin').value = nextMonthStr;
            
            return false;
        }
        
        function closeModal() {
            document.getElementById('abonnementModal').style.display = 'none';
        }
        
        // Handle form submission
        document.getElementById('abonnementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'subscribe');
            formData.append('ajax', '1');
            
            fetch('../../controller/AbonnementController.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    showNotification('🎉 Félicitations! Votre abonnement a été créé avec succès.', 'success');
                    closeModal();
                    setTimeout(() => {
                        window.location.href = 'abonnement.php';
                    }, 2000);
                } else {
                    showNotification('❌ Erreur: ' + res.message, 'error');
                }
            })
            .catch(err => {
                showNotification('❌ Erreur réseau. Veuillez réessayer.', 'error');
            });
        });
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 1000;
                max-width: 300px;
                background: ${type === 'success' ? '#10B981' : '#EF4444'};
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
