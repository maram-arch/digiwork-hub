<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = 'Accès refusé. Veuillez vous connecter en tant qu’administrateur.';
    header('Location: /view/front/login.php');
    exit;
}

require_once(__DIR__ . '/../../model/Pack.php');
$packModel = new Pack();
$packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION['flash'] ?? null;
if ($flash) unset($_SESSION['flash']);

$editPack = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editPack = $packModel->getById($id);
}

$totalPacks = is_array($packs) ? count($packs) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BO - Gestion des Packs | DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="assets/img/logo.png" alt="DigiWork HUB" style="height:56px;width:auto;display:block;margin:0 auto;filter:brightness(0) invert(1);">
            </div>
            <nav class="admin-sidebar-menu">
                <a href="dashboard_packs.php" class="admin-sidebar-item active">
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
            <header class="admin-topbar">
                <h1 class="admin-topbar-title">Gestion des Packs</h1>
                <div class="admin-topbar-actions">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Admin</span>
                    <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger); text-decoration: none;">Déconnexion</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($flash): ?>
                    <div class="admin-panel" style="margin-bottom:16px;border-left:6px solid var(--accent);">
                        <strong><?php echo htmlspecialchars($flash); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Total Packs</h4>
                            <div class="admin-stat-value"><?php echo (int)$totalPacks; ?></div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>CRUD</h4>
                            <div class="admin-stat-value">OK</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-screwdriver-wrench"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>État</h4>
                            <div class="admin-stat-value">Admin</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Palette</h4>
                            <div class="admin-stat-value">#10B981</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                    </div>
                </div>

                <div class="admin-panel" style="margin-bottom:16px;">
                    <h3 class="admin-panel-title"><?php echo $editPack ? 'Modifier le Pack' : 'Créer un Pack'; ?></h3>
                    <form method="POST" action="../../controller/PackController.php">
                        <input type="hidden" name="action" value="<?php echo $editPack ? 'update' : 'add'; ?>">
                        <input type="hidden" name="id-pack" value="<?php echo $editPack ? htmlspecialchars($editPack['id-pack']) : ''; ?>">

                        <div class="admin-form">
                            <div class="form-group">
                                <label>Nom du Pack</label>
                                <input type="text" name="nom" required value="<?php echo $editPack ? htmlspecialchars($editPack['nom-pack']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Prix</label>
                                <input type="number" step="0.01" name="prix" required value="<?php echo $editPack ? htmlspecialchars($editPack['prix']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Durée</label>
                                <input type="date" name="duree" required value="<?php echo $editPack ? htmlspecialchars($editPack['duree']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Nombre de projets max</label>
                                <input type="number" name="nb" required value="<?php echo $editPack ? htmlspecialchars($editPack['nb-proj-max']) : ''; ?>">
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label>Description</label>
                                <textarea name="description" rows="3" required><?php echo $editPack ? htmlspecialchars($editPack['description']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Support prioritaire</label>
                                <select name="support">
                                    <option value="oui" <?php echo $editPack && $editPack['support-prioritaire'] === 'oui' ? 'selected' : ''; ?>>Oui</option>
                                    <option value="non" <?php echo $editPack && $editPack['support-prioritaire'] === 'non' ? 'selected' : ''; ?>>Non</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
                            <?php if ($editPack): ?>
                                <button type="submit" class="btn-sm btn-info"><i class="fas fa-pen"></i> Mettre à jour</button>
                                <a href="dashboard_packs.php" class="btn-sm" style="background:#94A3B8;color:white;text-decoration:none;display:inline-flex;align-items:center;gap:8px;"><i class="fas fa-xmark"></i> Annuler</a>
                            <?php else: ?>
                                <button type="submit" class="btn-sm btn-accent"><i class="fas fa-plus"></i> Ajouter</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="admin-panel">
                    <h3 class="admin-panel-title">Tous les Packs</h3>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Durée</th>
                                    <th>Projets max</th>
                                    <th>Support</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packs as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['id-pack']); ?></td>
                                        <td style="font-weight:900;"><?php echo htmlspecialchars($p['nom-pack']); ?></td>
                                        <td><?php echo htmlspecialchars($p['prix']); ?></td>
                                        <td><?php echo htmlspecialchars($p['duree']); ?></td>
                                        <td><?php echo htmlspecialchars($p['nb-proj-max']); ?></td>
                                        <td><?php echo htmlspecialchars($p['support-prioritaire']); ?></td>
                                        <td style="white-space:nowrap;">
                                            <a class="btn-sm btn-info" href="dashboard_packs.php?edit=<?php echo htmlspecialchars($p['id-pack']); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                                                <i class="fas fa-pen"></i> Modifier
                                            </a>
                                            <a class="btn-sm btn-danger" href="../../controller/PackController.php?delete=1&id=<?php echo htmlspecialchars($p['id-pack']); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;margin-left:8px;">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
