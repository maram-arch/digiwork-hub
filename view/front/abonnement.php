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
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = 'Veuillez vous connecter pour accéder à votre profil.';
        header('Location: /view/front/login.php');
        exit;
    }
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
            <a href="abonnement.php" style="color: #fff; text-decoration: underline;">Profile</a>
            <span style="color: rgba(255,255,255,0.9); margin-left: 12px; font-weight:700;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
            <a href="../../controller/AuthController.php?action=logout" style="margin-left:8px;">Se déconnecter</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div style="max-width:900px;margin:20px auto;">
            <div style="background:#D1FAE5;color:#065F46;padding:12px;border-radius:8px;font-weight:bold;"><?= htmlspecialchars($flash) ?></div>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <!-- Current Subscriptions Section - More Prominent -->
        <div class="current-subscriptions" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(16,185,129,0.2);">
            <h2 style="color: white; margin: 0 0 20px 0; font-size: 24px;">
                <i class="fas fa-crown" style="margin-right: 10px;"></i>Mes Abonnements Actifs
            </h2>
            <div id="abonnements-container">
                <p style="color: rgba(255,255,255,0.9);">Chargement de vos abonnements...</p>
            </div>
        </div>

        <!-- Available Packs Section -->
        <div class="available-packs" style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="color: var(--primary); margin: 0 0 20px 0;">
                <i class="fas fa-box-open" style="margin-right: 10px;"></i>Disponible pour S'abonner
            </h2>
            <div id="packs-list">
                <p style="color: var(--text-muted);">Chargement des packs...</p>
            </div>
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
                    const statusColor = abo.status === 'actif' ? '#10B981' : (abo.status === 'expiré' ? '#EF4444' : '#F59E0B');
                    const statusBg = abo.status === 'actif' ? 'rgba(255,255,255,0.2)' : (abo.status === 'expiré' ? 'rgba(239,68,68,0.2)' : 'rgba(245,158,11,0.2)');
                    
                    html += `
                        <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; margin-bottom: 15px; backdrop-filter: blur(10px);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="color: white; margin: 0 0 10px 0; font-size: 18px;">
                                        <i class="fas fa-gem" style="margin-right: 8px;"></i>${abo['nom-pack']}
                                    </h3>
                                    <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px; line-height: 1.5;">
                                        <div style="margin-bottom: 5px;"><strong>Client :</strong> ${abo.nom}</div>
                                        <div style="margin-bottom: 5px;"><strong>Période :</strong> ${abo['date-deb']} → ${abo['date-fin']}</div>
                                        <div><strong>Téléphone :</strong> ${abo.tel}</div>
                                    </p>
                                </div>
                                <div>
                                    <span style="background: ${statusBg}; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 13px; border: 1px solid ${statusColor};">
                                        ${abo.status.toUpperCase()}
                                    </span>
                                </div>
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
            
            // Show loading state
            const button = e.target.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.textContent = 'Chargement...';
            button.disabled = true;
            
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
                    // Show success message instead of alert
                    showNotification('🎉 Félicitations! Votre abonnement a été créé avec succès.', 'success');
                    // Reload after a short delay to show the new abonnement
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showNotification('❌ Erreur: ' + res.message, 'error');
                    button.textContent = originalText;
                    button.disabled = false;
                }
            })
            .catch(err => {
                showNotification('❌ Erreur réseau. Veuillez réessayer.', 'error');
                button.textContent = originalText;
                button.disabled = false;
            });

            return false;
        }
        
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
