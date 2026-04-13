<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BO - Gestion des Packs</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
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
                    <div class="panel-title">Ajouter un nouveau Pack</div>
                    <form id="packForm">
                        <input type="hidden" id="action" name="action" value="add">
                        <input type="hidden" id="id-pack" name="id-pack" value="">
                        
                        <div class="admin-form">
                            <div class="form-group">
                                <label>Nom du Pack :</label>
                                <input type="text" id="nom" name="nom" required>
                            </div>
                            <div class="form-group">
                                <label>Prix (dt) :</label>
                                <input type="number" step="0.01" id="prix" name="prix" required>
                            </div>
                            <div class="form-group">
                                <label>Durée :</label>
                                <input type="text" id="duree" name="duree" required>
                            </div>
                            <div class="form-group">
                                <label>Nombre de projets max :</label>
                                <input type="number" id="nb" name="nb" required>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Description :</label>
                                <textarea id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Support Prioritaire :</label>
                                <select id="support" name="support">
                                    <option value="oui">Oui</option>
                                    <option value="non">Non</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn-sm" style="background: var(--accent);">Enregistrer le Pack</button>
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
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script src="pack_crud.js"></script>
</body>
</html>
