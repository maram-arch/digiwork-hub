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
    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    require_once(__DIR__ . '/../../model/Pack.php');
    
    $packModel = new Pack();
    $packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
    
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    if ($flash) unset($_SESSION['flash']);
    ?>

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="assets/img/logo.png" alt="DigiWork HUB" style="height:56px;width:auto;display:block;margin:0 auto;">
                <h2>DigiWork <span>HUB</span></h2>
                <p style="font-size: 12px; opacity: 0.7; margin-top: 8px;">Administration</p>
            </div>
            <nav class="admin-sidebar-menu">
                <a href="dashboard.php" class="admin-sidebar-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="#" class="admin-sidebar-item">
                    <i class="fas fa-users"></i>
                    <span>Gestion Utilisateurs</span>
                </a>
                <a href="dashboard_packs.php" class="admin-sidebar-item active">
                    <i class="fas fa-briefcase"></i>
                    <span>Projets & Offers (Packs)</span>
                </a>
                <a href="dashboard_abonnements.php" class="admin-sidebar-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Abonnements</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <h1 class="admin-topbar-title">Gestion des Packs</h1>
                <div class="admin-topbar-actions">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-envelope"></i>
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Admin</span>
                    <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger); text-decoration: none;">Déconnexion</a>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <!-- Flash Message -->
                <?php if ($flash): ?>
                    <div style="background: #FEF3C7; padding: 12px 20px; border-radius: 12px; color: #92400E; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                        <span><?= htmlspecialchars($flash) ?></span>
                        <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Packs Actifs</h4>
                            <div class="admin-stat-value" id="count-packs"><?= count($packs) ?></div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Revenus Totals</h4>
                            <div class="admin-stat-value">$120,500</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Abonnements</h4>
                            <div class="admin-stat-value" id="subscriptions-count">680</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-users"></i>
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

                <!-- Add/Edit Pack Panel -->
                <div class="admin-panel">
                    <h3 class="admin-panel-title" id="form-title">Ajouter un nouveau Pack</h3>
                    <form id="packForm">
                        <input type="hidden" id="action" name="action" value="add">
                        <input type="hidden" id="id-pack" name="id-pack" value="">
                        
                        <div class="admin-form">
                            <div class="form-group">
                                <label>Nom du Pack : <span style="color: red;">*</span></label>
                                <input type="text" id="nom" name="nom" maxlength="20" placeholder="ex: Premium, Basic, Pro">
                                <div class="error-message" id="nom-error">Le nom est requis (max 20 caractères)</div>
                            </div>
                            <div class="form-group">
                                <label>Prix (dt) : <span style="color: red;">*</span></label>
                                <input type="number" step="0.01" id="prix" name="prix" placeholder="0.00">
                                <div class="error-message" id="prix-error">Le prix doit être un nombre positif</div>
                            </div>
                            <div class="form-group">
                                <label>Durée (date de début recommandé) : <span style="color: red;">*</span></label>
                                <input type="date" id="duree" name="duree">
                                <div class="error-message" id="duree-error">La date est requise</div>
                            </div>
                            <div class="form-group">
                                <label>Nombre de projets max : <span style="color: red;">*</span></label>
                                <input type="number" id="nb" name="nb" min="1" max="999" placeholder="ex: 10">
                                <div class="error-message" id="nb-error">Entre 1 et 999 projets</div>
                            </div>
                            <div class="form-group full-width">
                                <label>Description : <span style="color: red;">*</span></label>
                                <textarea id="description" name="description" rows="3" maxlength="500" placeholder="Décrivez les avantages du pack..."></textarea>
                                <div class="error-message" id="description-error">La description est requise (max 500 caractères)</div>
                            </div>
                            <div class="form-group">
                                <label>Support Prioritaire : <span style="color: red;">*</span></label>
                                <select id="support" name="support">
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="oui">Oui</option>
                                    <option value="non">Non</option>
                                </select>
                                <div class="error-message" id="support-error">Veuillez sélectionner une option</div>
                            </div>
                        </div>
                        <div style="margin-top: 24px; display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Enregistrer le Pack</button>
                            <button type="button" class="btn" id="cancel-btn" style="background: #9CA3AF; color: white; display: none;" onclick="resetForm()">Annuler</button>
                        </div>
                    </form>
                </div>

                <!-- Packs List Panel -->
                <div class="admin-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <h3 class="admin-panel-title" style="margin-bottom: 0;">Liste des Packs Récents</h3>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="searchInput" placeholder="Rechercher..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <select id="sortSelect" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="price-desc">Prix (élevé)</option>
                                <option value="price-asc">Prix (bas)</option>
                                <option value="name-asc">Nom (A-Z)</option>
                                <option value="name-desc">Nom (Z-A)</option>
                                <option value="projects">Projets Max</option>
                            </select>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="packs-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Durée</th>
                                    <th>Projets Max</th>
                                    <th>Support</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="packs-tbody">
                                <tr><td colspan="7" style="text-align: center;">Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `admin-toast ${type === 'error' ? 'error' : ''}`;
            toast.innerHTML = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Form validation
        function validateForm() {
            let isValid = true;
            
            document.querySelectorAll('.form-error').forEach(el => el.classList.remove('form-error'));
            document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
            
            const nom = document.getElementById('nom').value.trim();
            if (!nom || nom.length === 0) {
                document.getElementById('nom').classList.add('form-error');
                document.getElementById('nom-error').classList.add('show');
                isValid = false;
            } else if (nom.length > 20) {
                document.getElementById('nom').classList.add('form-error');
                document.getElementById('nom-error').textContent = 'Max 20 caractères';
                document.getElementById('nom-error').classList.add('show');
                isValid = false;
            }
            
            const prix = parseFloat(document.getElementById('prix').value);
            if (isNaN(prix) || prix <= 0) {
                document.getElementById('prix').classList.add('form-error');
                document.getElementById('prix-error').classList.add('show');
                isValid = false;
            }
            
            const duree = document.getElementById('duree').value;
            if (!duree) {
                document.getElementById('duree').classList.add('form-error');
                document.getElementById('duree-error').classList.add('show');
                isValid = false;
            }
            
            const nb = parseInt(document.getElementById('nb').value);
            if (isNaN(nb) || nb < 1 || nb > 999) {
                document.getElementById('nb').classList.add('form-error');
                document.getElementById('nb-error').classList.add('show');
                isValid = false;
            }
            
            const description = document.getElementById('description').value.trim();
            if (!description || description.length === 0) {
                document.getElementById('description').classList.add('form-error');
                document.getElementById('description-error').classList.add('show');
                isValid = false;
            } else if (description.length > 500) {
                document.getElementById('description').classList.add('form-error');
                document.getElementById('description-error').textContent = 'Max 500 caractères';
                document.getElementById('description-error').classList.add('show');
                isValid = false;
            }
            
            const support = document.getElementById('support').value;
            if (!support || (support !== 'oui' && support !== 'non')) {
                document.getElementById('support').classList.add('form-error');
                document.getElementById('support-error').classList.add('show');
                isValid = false;
            }
            
            return isValid;
        }
        
        // Submit form
        async function submitForm(event) {
            event.preventDefault();
            
            if (!validateForm()) {
                showToast('Veuillez corriger les erreurs', 'error');
                return;
            }
            
            const form = document.getElementById('packForm');
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn ? submitBtn.textContent : '';
            
            if (submitBtn) {
                submitBtn.textContent = 'Envoi en cours...';
                submitBtn.disabled = true;
            }
            
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            try {
                const response = await fetch('../../controller/PackController.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast(data.message);
                    resetForm();
                    await loadPacks();
                } else {
                    showToast(data.message || 'Erreur', 'error');
                }
            } catch (error) {
                showToast('Erreur réseau', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            }
        }
        
        // Escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        let allPacks = [];
        
        // Load packs
        async function loadPacks() {
            try {
                const response = await fetch('../../controller/PackController.php?action=getAll');
                const packs = await response.json();
                allPacks = packs;
                
                document.getElementById('count-packs').innerText = packs.length;
                renderPacks(packs);
            } catch (error) {
                console.error(error);
            }
        }
        
        function renderPacks(packs) {
            const tbody = document.getElementById('packs-tbody');
            
            if (packs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Aucun pack</td></tr>';
                return;
            }
            
            let html = '';
            packs.forEach(p => {
                html += `
                    <tr id="pack-${p['id-pack']}">
                        <td>${p['id-pack']}</td>
                        <td><strong>${escapeHtml(p['nom-pack'])}</strong></td>
                        <td><span style="color: var(--primary); font-weight: bold;">${p.prix} dt</span></td>
                        <td>${escapeHtml(p.duree)}</td>
                        <td>${p['nb-proj-max']} Max</td>
                        <td>${escapeHtml(p['support-prioritaire'])}</td>
                        <td>
                            <button class="btn btn-sm" onclick='editPack(${JSON.stringify(p)})' style="background: #3B82F6; color: white; margin-right: 8px;">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deletePack(${p['id-pack']})">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
        
        function filterAndSortPacks() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortValue = document.getElementById('sortSelect').value;
            
            let filtered = allPacks.filter(pack => {
                return pack['nom-pack']?.toLowerCase().includes(searchTerm) ||
                       pack.description?.toLowerCase().includes(searchTerm) ||
                       pack.prix?.toString().includes(searchTerm) ||
                       pack['support-prioritaire']?.toLowerCase().includes(searchTerm);
            });
            
            // Sort
            filtered.sort((a, b) => {
                switch(sortValue) {
                    case 'price-desc':
                        return parseFloat(b.prix) - parseFloat(a.prix);
                    case 'price-asc':
                        return parseFloat(a.prix) - parseFloat(b.prix);
                    case 'name-asc':
                        return a['nom-pack']?.localeCompare(b['nom-pack']);
                    case 'name-desc':
                        return b['nom-pack']?.localeCompare(a['nom-pack']);
                    case 'projects':
                        return parseInt(b['nb-proj-max']) - parseInt(a['nb-proj-max']);
                    default:
                        return 0;
                }
            });
            
            renderPacks(filtered);
        }
        
        // Add event listeners for search and sort
        document.getElementById('searchInput').addEventListener('input', filterAndSortPacks);
        document.getElementById('sortSelect').addEventListener('change', filterAndSortPacks);
        
        // Reset form
        function resetForm() {
            document.getElementById('packForm').reset();
            document.getElementById('action').value = 'add';
            document.getElementById('id-pack').value = '';
            document.getElementById('form-title').textContent = 'Ajouter un nouveau Pack';
            document.getElementById('submit-btn').textContent = 'Enregistrer le Pack';
            document.getElementById('cancel-btn').style.display = 'none';
            
            document.querySelectorAll('.form-error').forEach(el => el.classList.remove('form-error'));
            document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
        }
        
        // Edit pack
        function editPack(pack) {
            document.getElementById('action').value = 'update';
            document.getElementById('id-pack').value = pack['id-pack'];
            document.getElementById('nom').value = pack['nom-pack'];
            document.getElementById('prix').value = pack['prix'];
            document.getElementById('duree').value = pack['duree'];
            document.getElementById('description').value = pack['description'];
            document.getElementById('nb').value = pack['nb-proj-max'];
            document.getElementById('support').value = pack['support-prioritaire'];
            document.getElementById('form-title').textContent = 'Modifier le Pack';
            document.getElementById('submit-btn').textContent = 'Mettre à jour';
            document.getElementById('cancel-btn').style.display = 'inline-block';
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Delete pack
        async function deletePack(id) {
            if (!confirm('⚠️ Supprimer ce pack définitivement ?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                formData.append('ajax', '1');
                
                const response = await fetch('../../controller/PackController.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast('Pack supprimé');
                    document.getElementById('pack-' + id).remove();
                    let count = parseInt(document.getElementById('count-packs').innerText);
                    document.getElementById('count-packs').innerText = count - 1;
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Erreur', 'error');
            }
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        // Real-time validation
        document.getElementById('nom').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('form-error');
                document.getElementById('nom-error').classList.remove('show');
            }
        });
        
        document.getElementById('prix').addEventListener('input', function() {
            if (parseFloat(this.value) > 0) {
                this.classList.remove('form-error');
                document.getElementById('prix-error').classList.remove('show');
            }
        });
        
        document.getElementById('packForm').addEventListener('submit', submitForm);
        
        // Load subscriptions count
        async function loadSubscriptionsCount() {
            try {
                const response = await fetch('../../controller/AbonnementController.php?action=getAll');
                const subs = await response.json();
                document.getElementById('subscriptions-count').innerText = subs.length;
            } catch(e) {}
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            loadSubscriptionsCount();
            loadPacks();
        });
    </script>
</body>
</html>
