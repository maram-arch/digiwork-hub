<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BO - Gestion des Packs</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php
    // Server-rendered Back Office Packs dashboard (non-AJAX forms)
    session_start();
    require_once(__DIR__ . '/../../model/Pack.php');

    $packModel = new Pack();
    $packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

    // flash message
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    if ($flash) unset($_SESSION['flash']);

    // Edit mode
    $editPack = null;
    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $editPack = $packModel->getById($id);
    }

    ?>

    <div class="back-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="../frontoffice/assets/img/logo/digiwork-hub.png" alt="DigiWork HUB" style="height:40px;">
            </div>
            <div class="sidebar-menu">
                <a href="#" class="sidebar-item">
                    <i>📊</i> Tableau de Bord
                </a>
                <a href="#" class="sidebar-item">
                    <i>👥</i> Gestion Utilisateurs
                </a>
                <a href="dashboard_packs.php" class="sidebar-item active">
                    <i>💼</i> Projets & Offers (Packs)
                </a>
                <a href="dashboard_abonnements.php" class="sidebar-item">
                    <i>💳</i> Abonnements
                </a>
            </div>
        </div>
        
        <div class="main-wrapper">
            <div class="topbar">
                <span>Admin | Messages ▾ | Profil ▾</span>
            </div>

            <div class="content">
                <?php if ($flash): ?>
                    <div style="margin-bottom:16px;"><div style="background:#FEF3C7;padding:12px;border-radius:8px;color:#92400E;font-weight:700;"><?= htmlspecialchars($flash) ?></div></div>
                <?php endif; ?>
                <div class="stat-cards">
                    <div class="stat-card blue">
                        <div>
                            <div class="stat-title">Packs Actifs</div>
                            <div class="stat-value" id="count-packs">0</div>
                        </div>
                        <i style="font-size: 30px;">💼</i>
                    </div>
                    <div class="stat-card green">
                        <div>
                            <div class="stat-title">Revenus Totals</div>
                            <div class="stat-value">$120,500</div>
                        </div>
                        <i style="font-size: 30px;">💰</i>
                    </div>
                    <div class="stat-card light-green">
                        <div>
                            <div class="stat-title">Abonnements</div>
                            <div class="stat-value">680</div>
                        </div>
                        <i style="font-size: 30px;">📈</i>
                    </div>
                    <div class="stat-card dark-green">
                        <div>
                            <div class="stat-title">Score Durabilité</div>
                            <div class="stat-value">82</div>
                        </div>
                        <i style="font-size: 30px;">⏱️</i>
                    </div>
                </div>

                <div class="dashboard-panel">
                    <div class="panel-title"><?= $editPack ? 'Modifier le Pack' : 'Ajouter un nouveau Pack' ?></div>
                    <form method="POST" action="../../controller/PackController.php">
                        <input type="hidden" name="action" value="<?= $editPack ? 'update' : 'add' ?>">
                        <input type="hidden" id="id-pack" name="id-pack" value="<?= $editPack ? htmlspecialchars($editPack['id-pack']) : '' ?>">
                        
                        <div class="admin-form">
                            <div class="form-group">
                                <label>Nom du Pack :</label>
                                <input type="text" id="nom" name="nom" required value="<?= $editPack ? htmlspecialchars($editPack['nom-pack']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Prix (dt) :</label>
                                <input type="number" step="0.01" id="prix" name="prix" required value="<?= $editPack ? htmlspecialchars($editPack['prix']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Durée (date de début recommandé) :</label>
                                <input type="date" id="duree" name="duree" required value="<?= $editPack ? htmlspecialchars($editPack['duree']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Nombre de projets max :</label>
                                <input type="number" id="nb" name="nb" required value="<?= $editPack ? htmlspecialchars($editPack['nb-proj-max']) : '' ?>">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Description :</label>
                                <textarea id="description" name="description" rows="3" required><?= $editPack ? htmlspecialchars($editPack['description']) : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Support Prioritaire :</label>
                                <select id="support" name="support">
                                    <option value="oui" <?= $editPack && $editPack['support-prioritaire'] === 'oui' ? 'selected' : '' ?>>Oui</option>
                                    <option value="non" <?= $editPack && $editPack['support-prioritaire'] === 'non' ? 'selected' : '' ?>>Non</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:12px;display:flex;gap:12px;">
                            <button type="submit" class="btn-sm" style="background: var(--accent);"><?= $editPack ? 'Mettre à jour' : 'Enregistrer le Pack' ?></button>
                            <?php if ($editPack): ?>
                                <a href="dashboard_packs.php" class="btn-sm" style="background:#94A3B8;color:white;padding:8px 12px;border-radius:6px;text-decoration:none;">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="dashboard-panel">
                    <div class="panel-title">Liste des Packs Récents</div>
                    <table class="admin-table" id="packs-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prix</th>
                                <th>Projets Max</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packs as $p): ?>
                                <tr id="pack-<?= htmlspecialchars($p['id-pack']) ?>">
                                    <td><?= htmlspecialchars($p['id-pack']) ?></td>
                                    <td style="font-weight:bold;"><?= htmlspecialchars($p['nom-pack']) ?></td>
                                    <td style="color:var(--green-card); font-weight:bold;"><?= htmlspecialchars($p['prix']) ?> dt</td>
                                    <td><?= htmlspecialchars($p['nb-proj-max']) ?> Max</td>
                                    <td>
                                        <a class="btn-sm" href="dashboard_packs.php?edit=<?= htmlspecialchars($p['id-pack']) ?>" style="background:#3B82F6;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;margin-right:8px;">Modifier</a>
                                        <a class="btn-sm" href="../../controller/PackController.php?delete=1&id=<?= htmlspecialchars($p['id-pack']) ?>" style="background:var(--danger-color);color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Non-AJAX server-rendered CRUD; JS file not required here -->
</body>
</html>
</body>
</html>
