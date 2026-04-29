<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = 'Accès refusé. Veuillez vous connecter en tant qu’administrateur.';
    header('Location: /view/front/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BO - Gestion des Abonnements | DigiWork HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="assets/img/logo.png" alt="DigiWork HUB" style="height:56px;width:auto;display:block;margin:0 auto;">
            </div>
            <nav class="admin-sidebar-menu">
                <a href="dashboard_packs.php" class="admin-sidebar-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Packs</span>
                </a>
                <a href="dashboard_abonnements.php" class="admin-sidebar-item active">
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
                <h1 class="admin-topbar-title">Gestion des Abonnements</h1>
                <div class="admin-topbar-actions">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Admin</span>
                    <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger); text-decoration: none;">Déconnexion</a>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Total Abonnements</h4>
                            <div class="admin-stat-value" id="total-subs">0</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Abonnements Actifs</h4>
                            <div class="admin-stat-value" id="active-subs">0</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Expirés</h4>
                            <div class="admin-stat-value" id="expired-subs">0</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <h4>Revenus Mensuels</h4>
                            <div class="admin-stat-value">$12,450</div>
                        </div>
                        <div class="admin-stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>

                <div class="admin-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <h3 class="admin-panel-title" style="margin-bottom: 0;">Tous les Abonnements</h3>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="searchInput" placeholder="Rechercher..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <select id="sortSelect" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="date-desc">Date (récent)</option>
                                <option value="date-asc">Date (ancien)</option>
                                <option value="name-asc">Nom (A-Z)</option>
                                <option value="name-desc">Nom (Z-A)</option>
                                <option value="status">Statut</option>
                            </select>
                            <button onclick="refreshSubscriptions()" class="btn btn-sm" style="background: var(--primary); color: white;">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="abo-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Pack</th>
                                    <th>Date Début</th>
                                    <th>Date Fin</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="abo-tbody">
                                <tr><td colspan="8" style="text-align: center;">Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
            <h3 style="margin-top: 0;">Modifier l'abonnement</h3>
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                
                <div style="margin-bottom: 15px;">
                    <label for="editStatus" style="display: block; margin-bottom: 5px; font-weight: bold;">Statut:</label>
                    <select id="editStatus" name="status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="actif">Actif</option>
                        <option value="expiré">Expiré</option>
                        <option value="en_attente">En attente</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="editDateFin" style="display: block; margin-bottom: 5px; font-weight: bold;">Date de fin:</label>
                    <input type="date" id="editDateFin" name="date_fin" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="closeEditModal()" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 10px;">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background: var(--primary); color: white;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `admin-toast ${type === 'error' ? 'error' : ''}`;
            toast.innerHTML = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function getStatusClass(status) {
            switch(status) {
                case 'actif': return 'status-actif';
                case 'expiré': return 'status-expire';
                default: return 'status-en_attente';
            }
        }
        
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            return new Date(dateStr).toLocaleDateString('fr-FR');
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
        
        let allSubscriptions = [];
        
        async function loadSubscriptions() {
            try {
                const response = await fetch('../../controller/AbonnementController.php?action=getAll');
                const subs = await response.json();
                allSubscriptions = subs;
                
                const total = subs.length;
                const active = subs.filter(s => s.status === 'actif').length;
                const expired = subs.filter(s => s.status === 'expiré' || (s.status === 'actif' && new Date(s['date-fin']) < new Date())).length;
                
                document.getElementById('total-subs').innerText = total;
                document.getElementById('active-subs').innerText = active;
                document.getElementById('expired-subs').innerText = expired;
                
                renderSubscriptions(subs);
            } catch (error) {
                console.error(error);
                showToast('Erreur de chargement', 'error');
            }
        }
        
        function renderSubscriptions(subs) {
            const tbody = document.getElementById('abo-tbody');
            if (subs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Aucun abonnement</td></tr>';
                return;
            }
            
            let html = '';
            subs.forEach(sub => {
                const displayStatus = sub.status;
                const statusClass = getStatusClass(displayStatus);
                const statusText = displayStatus === 'actif' ? 'Actif' : (displayStatus === 'expiré' ? 'Expiré' : 'En attente');
                
                html += `
                    <tr id="abo-${sub['id-abonnement']}">
                        <td>${sub['id-abonnement']}</td>
                        <td><strong>${escapeHtml(sub.nom)}</strong></td>
                        <td>${escapeHtml(sub.tel)}</td>
                        <td><span style="color: var(--primary);">${escapeHtml(sub['nom-pack'])}</span></td>
                        <td>${formatDate(sub['date-deb'])}</td>
                        <td>${formatDate(sub['date-fin'])}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editAbo(${sub['id-abonnement']}, '${escapeHtml(sub.status)}', '${escapeHtml(sub['date-fin'])}')" style="margin-right: 5px;">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteAbo(${sub['id-abonnement']})">
                                <i class="fas fa-ban"></i> Révoquer
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
        
        function filterAndSortSubscriptions() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortValue = document.getElementById('sortSelect').value;
            
            let filtered = allSubscriptions.filter(sub => {
                return sub.nom?.toLowerCase().includes(searchTerm) ||
                       sub.tel?.toString().includes(searchTerm) ||
                       sub['nom-pack']?.toLowerCase().includes(searchTerm) ||
                       sub.status?.toLowerCase().includes(searchTerm);
            });
            
            // Sort
            filtered.sort((a, b) => {
                switch(sortValue) {
                    case 'date-desc':
                        return new Date(b['date-deb']) - new Date(a['date-deb']);
                    case 'date-asc':
                        return new Date(a['date-deb']) - new Date(b['date-deb']);
                    case 'name-asc':
                        return a.nom?.localeCompare(b.nom);
                    case 'name-desc':
                        return b.nom?.localeCompare(a.nom);
                    case 'status':
                        return a.status?.localeCompare(b.status);
                    default:
                        return 0;
                }
            });
            
            renderSubscriptions(filtered);
        }
        
        // Add event listeners for search and sort
        document.getElementById('searchInput').addEventListener('input', filterAndSortSubscriptions);
        document.getElementById('sortSelect').addEventListener('change', filterAndSortSubscriptions);
        
        async function deleteAbo(id) {
            if (!confirm('⚠️ Révoquer cet abonnement ?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                formData.append('ajax', '1');
                
                const response = await fetch('../../controller/AbonnementController.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast('Abonnement révoqué');
                    document.getElementById('abo-' + id).remove();
                    loadSubscriptions();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Erreur', 'error');
            }
        }
        
        function editAbo(id, status, dateFin) {
            document.getElementById('editId').value = id;
            document.getElementById('editStatus').value = status;
            document.getElementById('editDateFin').value = dateFin;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Handle edit form submission
        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update');
            formData.append('ajax', '1');
            
            try {
                const response = await fetch('../../controller/AbonnementController.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast('Abonnement mis à jour avec succès');
                    closeEditModal();
                    loadSubscriptions();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Erreur réseau', 'error');
            }
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
        
        function refreshSubscriptions() {
            showToast('Actualisation...');
            loadSubscriptions();
        }
        
        document.addEventListener('DOMContentLoaded', loadSubscriptions);
    </script>
</body>
</html>
