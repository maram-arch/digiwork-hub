<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = 'Accès refusé. Veuillez vous connecter en tant qu\'administrateur.';
    header('Location: /view/front/login.php');
    exit;
}

require_once(__DIR__ . '/../../model/Abonnement.php');
require_once(__DIR__ . '/../../model/Pack.php');

$abonnementModel = new Abonnement();
$packModel = new Pack();

// Get statistics
$totalAbonnements = count($abonnementModel->getAll()->fetchAll());
$activeAbonnements = count($abonnementModel->getAll()->fetchAll(PDO::FETCH_ASSOC));
$totalPacks = count($packModel->getAll()->fetchAll());
$totalRevenue = 0; // You can calculate this from abonnement data

// Get recent activity
$recentAbonnements = $abonnementModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
$packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { background-color: #FFFFFF !important; }
        .admin-container { background-color: #FFFFFF !important; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border: 1px solid #dddddd;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #333333;
            margin-bottom: 8px;
        }
        .stat-label {
            color: #666666;
            font-size: 14px;
            font-weight: 600;
        }
        .main-content {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #dddddd;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }
        .recent-activity {
            margin-top: 30px;
        }
        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }
        .activity-content {
            flex: 1;
        }
        .activity-title {
            font-weight: 600;
            color: #333333;
            font-size: 14px;
        }
        .activity-time {
            color: #666666;
            font-size: 12px;
        }
    </style>
</head>
<body style="background-color: #FFFFFF !important;">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="assets/img/logo.png" alt="DigiWork HUB" style="height:56px;width:auto;display:block;margin:0 auto;">
            </div>
            <nav class="admin-sidebar-menu">
                <a href="dashboard.php" class="admin-sidebar-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="dashboard_packs.php" class="admin-sidebar-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Packs</span>
                </a>
                <a href="dashboard_abonnements.php" class="admin-sidebar-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Abonnements</span>
                </a>
                <div style="margin: 12px 12px 6px; border-top: 1px solid rgba(255,255,255,.12); padding-top: 12px;">
                    <a href="/view/front/packs.php" class="admin-sidebar-item" style="background:rgba(16,185,129,.08);">
                        <i class="fas fa-globe"></i>
                        <span>Front Office</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Tableau de Bord</h1>
                <div class="admin-header-actions">
                    <span style="color: #64748B; margin-right: 12px;">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                    </span>
                    <a href="../../controller/AuthController.php?action=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <div class="main-content">
                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f6ef; color: #00A651;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?= $totalAbonnements ?></div>
                        <div class="stat-label">Total Abonnements</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f6ef; color: #00A651;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-value"><?= $activeAbonnements ?></div>
                        <div class="stat-label">Abonnements Actifs</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f6ef; color: #00A651;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-value"><?= $totalPacks ?></div>
                        <div class="stat-label">Packs Disponibles</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f6ef; color: #00A651;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value"><?= $totalRevenue ?>€</div>
                        <div class="stat-label">Revenus Totaux</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h2 style="margin-bottom: 20px; color: #333333;">
                        <i class="fas fa-clock" style="margin-right: 8px;"></i>Activité Récente
                    </h2>
                    
                    <?php if (empty($recentAbonnements)): ?>
                        <p style="color: #666666; text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i><br>
                            Aucune activité récente
                        </p>
                    <?php else: ?>
                        <?php foreach (array_slice($recentAbonnements, 0, 5) as $abo): ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #e8f6ef; color: #00A651;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        Nouvel abonnement: <?= htmlspecialchars($abo['nom-pack']) ?>
                                    </div>
                                    <div class="activity-time">
                                        Client: <?= htmlspecialchars($abo['nom']) ?> • 
                                        <?= htmlspecialchars($abo['date-deb']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #dddddd;">
                    <h3 style="margin-bottom: 20px; color: #333333;">
                        <i class="fas fa-bolt" style="margin-right: 8px;"></i>Actions Rapides
                    </h3>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="dashboard_packs.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px; background: linear-gradient(135deg, #00A651 0%, #008040 100%); color: white; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Ajouter un Pack
                        </a>
                        <a href="dashboard_abonnements.php" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; background: #666666; color: white; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-list"></i> Voir les Abonnements
                        </a>
                        <a href="/view/front/packs.php" class="btn-outline" style="text-decoration: none; padding: 10px 20px; background: white; color: #00A651; border: 2px solid #00A651; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-eye"></i> Voir le Front Office
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>