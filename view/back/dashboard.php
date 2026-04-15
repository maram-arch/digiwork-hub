<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>/* small inline fallback to avoid totally broken layout if CSS not found */
        body{font-family:Inter,system-ui,Arial,Helvetica,sans-serif;background:#f7f9fb;margin:0}
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="../frontoffice/assets/img/logo/logo.png" alt="DigiWork HUB" onerror="this.src='https://via.placeholder.com/120x45?text=DigiWork+HUB'">
                <h2>DigiWork <span>HUB</span></h2>
                <p style="font-size: 12px; opacity: 0.7; margin-top: 8px;">Administration</p>
            </div>
            <nav class="admin-sidebar-menu">
                <a href="dashboard.php" class="admin-sidebar-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="#" class="admin-sidebar-item">
                    <i class="fas fa-users"></i>
                    <span>Gestion Utilisateurs</span>
                </a>
                <a href="dashboard_packs.php" class="admin-sidebar-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Projets & Offres</span>
                </a>
                <a href="#" class="admin-sidebar-item">
                    <i class="fas fa-robot"></i>
                    <span>IA Matching</span>
                </a>
                <a href="#" class="admin-sidebar-item">
                    <i class="fas fa-star"></i>
                    <span>Évaluations</span>
                </a>
                <a href="dashboard_abonnements.php" class="admin-sidebar-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Abonnements</span>
                </a>
                <a href="#" class="admin-sidebar-item">
                    <i class="fas fa-leaf"></i>
                    <span>Durabilité</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <h1 class="admin-topbar-title">Tableau de Bord</h1>
                <div class="admin-topbar-actions">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-envelope"></i>
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Admin</span>
                    <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger);">Déconnexion</a>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Projets Totaux</h4>
                            <div class="admin-stat-value">1,284</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Utilisateurs</h4>
                            <div class="admin-stat-value">3,642</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Abonnements Actifs</h4>
                            <div class="admin-stat-value" id="active-subs">-</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Score Durabilité</h4>
                            <div class="admin-stat-value">82</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                    <!-- Left: Répartition des Clients -->
                    <div class="admin-panel">
                        <h3 class="admin-panel-title">Répartition des Clients</h3>
                        <div style="text-align: center; padding: 20px;">
                            <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 20px;">
                                <div style="text-align: center;">
                                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <span style="color: white; font-size: 24px; font-weight: bold;">68%</span>
                                    </div>
                                    <p style="margin-top: 10px;">Local</p>
                                </div>
                                <div style="text-align: center;">
                                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--secondary); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <span style="color: white; font-size: 24px; font-weight: bold;">32%</span>
                                    </div>
                                    <p style="margin-top: 10px;">International</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Messagerie / Activité récente -->
                    <div class="admin-panel">
                        <h3 class="admin-panel-title">Activité Récente</h3>
                        <div style="margin-top: 16px;">
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #E5E7EB;">
                                <div style="width: 40px; height: 40px; background: #E0F2FE; border-radius: 50%; display: flex; align-items: center; justify-content: center;">🎨</div>
                                <div><strong>Amine B.</strong><br><small>Designer UX • Nouveau projet</small></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #E5E7EB;">
                                <div style="width: 40px; height: 40px; background: #FCE7F3; border-radius: 50%; display: flex; align-items: center; justify-content: center;">📝</div>
                                <div><strong>Line S.</strong><br><small>Rédactrice • Mission complétée</small></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0;">
                                <div style="width: 40px; height: 40px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">💻</div>
                                <div><strong>Youssef K.</strong><br><small>Développeur • Nouveau client</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projets Récents -->
                <div class="admin-panel">
                    <h3 class="admin-panel-title">Projets Récents</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Client</th>
                                <th>Budget</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Développement App Mobile</td>
                                <td>TechCorp</td>
                                <td>3,200 dt</td>
                                <td><span style="color: var(--warning);">En cours</span></td>
                                <td><button class="btn btn-sm btn-primary">Voir</button></td>
                            </tr>
                            <tr>
                                <td>Vidéo Promotionnelle</td>
                                <td>GreenStart</td>
                                <td>1,200 dt</td>
                                <td><span style="color: var(--success);">Terminé</span></td>
                                <td><button class="btn btn-sm btn-primary">Voir</button></td>
                            </tr>
                            <tr>
                                <td>Audit SEO Durable</td>
                                <td>EcoCommerce</td>
                                <td>950 dt</td>
                                <td><span style="color: var(--warning);">En attente</span></td>
                                <td><button class="btn btn-sm btn-primary">Voir</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Load active subscriptions count (robustly handles non-JSON responses)
        async function loadActiveSubscriptions() {
            try {
                const response = await fetch('../../controller/AbonnementController.php?action=getAll');
                if (!response.ok) throw new Error('Network response not ok: ' + response.status);
                const text = await response.text();
                let subs = [];
                try {
                    subs = JSON.parse(text);
                } catch (e) {
                    // server returned non-JSON (could be a PHP warning); treat as empty
                    subs = [];
                }
                const activeCount = Array.isArray(subs) ? subs.filter(s => s.status === 'actif').length : 0;
                document.getElementById('active-subs').innerText = activeCount;
            } catch (error) {
                console.error('Failed to load subscriptions:', error);
                const el = document.getElementById('active-subs');
                if (el) el.innerText = '0';
            }
        }
        
        document.addEventListener('DOMContentLoaded', loadActiveSubscriptions);
    </script>
</body>
</html>
