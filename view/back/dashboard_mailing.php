<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = 'Accès refusé. Veuillez vous connecter en tant qu\'administrateur.';
    header('Location: /view/front/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BO - Mailing | DigiWork HUB</title>
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
                <a href="dashboard.php" class="admin-sidebar-item">
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
                <a href="dashboard_mailing.php" class="admin-sidebar-item active">
                    <i class="fas fa-envelope"></i>
                    <span>Mailing</span>
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
                <h1 class="admin-topbar-title">Système de Mailing</h1>
                <div class="admin-topbar-actions">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Admin</span>
                    <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger); text-decoration: none;">Déconnexion</a>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-panel">
                    <h3 class="admin-panel-title">Envoyer un Email de Masse</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                        <!-- Recipients Selection -->
                        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                            <h4 style="margin-top: 0;">Sélection des Destinataires</h4>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Type de destinataires:</label>
                                <select id="recipientType" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="all">Tous les utilisateurs</option>
                                    <option value="active">Abonnements actifs</option>
                                    <option value="expiring">Abonnements expirant (7 jours)</option>
                                </select>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <button onclick="loadRecipients()" class="btn btn-sm" style="background: var(--primary); color: white; width: 100%;">
                                    <i class="fas fa-users"></i> Charger les destinataires
                                </button>
                            </div>
                            
                            <div id="recipientsList" style="max-height: 300px; overflow-y: auto; background: white; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
                                <p style="color: #666; text-align: center;">Aucun destinataire sélectionné</p>
                            </div>
                            
                            <div style="margin-top: 10px; font-size: 14px; color: #666;">
                                <strong>Total:</strong> <span id="recipientCount">0</span> destinataires
                            </div>
                        </div>
                        
                        <!-- Email Composition -->
                        <div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Sujet:</label>
                                <input type="text" id="emailSubject" placeholder="Sujet de l'email" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Message:</label>
                                <textarea id="emailMessage" rows="10" placeholder="Votre message... Utilisez {nom} pour le nom du client, {pack} pour le pack, {date_fin} pour la date de fin" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
                            </div>
                            
                            <div style="margin-bottom: 15px; background: #e8f6ef; padding: 10px; border-radius: 4px; font-size: 13px;">
                                <strong>Variables disponibles:</strong><br>
                                {nom} - Nom du client<br>
                                {email} - Email du client<br>
                                {pack} - Nom du pack<br>
                                {date_fin} - Date de fin d'abonnement
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button onclick="sendBulkEmail()" class="btn btn-primary" style="flex: 1;">
                                    <i class="fas fa-paper-plane"></i> Envoyer l'email
                                </button>
                                <button onclick="previewEmail()" class="btn btn-sm" style="background: #6c757d; color: white;">
                                    <i class="fas fa-eye"></i> Aperçu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email History -->
                <div class="admin-panel" style="margin-top: 20px;">
                    <h3 class="admin-panel-title">Historique des Emails</h3>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Sujet</th>
                                    <th>Destinataires</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="emailHistory">
                                <tr><td colspan="4" style="text-align: center;">Aucun email envoyé</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 600px; max-width: 90%; max-height: 80vh; overflow-y: auto;">
            <h3 style="margin-top: 0;">Aperçu de l'Email</h3>
            <div id="previewContent" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin: 15px 0;"></div>
            <div style="text-align: right;">
                <button onclick="closePreviewModal()" class="btn btn-sm" style="background: #6c757d; color: white;">Fermer</button>
            </div>
        </div>
    </div>

    <script>
        let selectedRecipients = [];
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `admin-toast ${type === 'error' ? 'error' : ''}`;
            toast.innerHTML = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        async function loadRecipients() {
            const type = document.getElementById('recipientType').value;
            
            if (!type) {
                showToast('Veuillez sélectionner un type de destinataires', 'error');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', type === 'all' ? 'getUsers' : (type === 'active' ? 'getActiveSubscribers' : 'getExpiringSubscribers'));
                
                const response = await fetch('../../controller/MailingController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    selectedRecipients = data.data;
                    displayRecipients(selectedRecipients);
                } else {
                    showToast('Erreur lors du chargement des destinataires', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Erreur réseau', 'error');
            }
        }
        
        function displayRecipients(recipients) {
            const container = document.getElementById('recipientsList');
            document.getElementById('recipientCount').textContent = recipients.length;
            
            if (recipients.length === 0) {
                container.innerHTML = '<p style="color: #666; text-align: center;">Aucun destinataire trouvé</p>';
                return;
            }
            
            let html = '';
            recipients.forEach(r => {
                html += `
                    <div style="padding: 8px; border-bottom: 1px solid #eee; display: flex; align-items: center;">
                        <i class="fas fa-user" style="color: var(--primary); margin-right: 10px;"></i>
                        <div>
                            <div style="font-weight: 600;">${r.email}</div>
                            <div style="font-size: 12px; color: #666;">${r['nom-pack'] ? r['nom-pack'] : 'Utilisateur'}</div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
        
        async function sendBulkEmail() {
            const subject = document.getElementById('emailSubject').value.trim();
            const message = document.getElementById('emailMessage').value.trim();
            
            if (selectedRecipients.length === 0) {
                showToast('Veuillez sélectionner des destinataires', 'error');
                return;
            }
            
            if (!subject || !message) {
                showToast('Veuillez remplir le sujet et le message', 'error');
                return;
            }
            
            if (!confirm(`Envoyer cet email à ${selectedRecipients.length} destinataires?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'sendBulk');
                formData.append('recipients', JSON.stringify(selectedRecipients));
                formData.append('subject', subject);
                formData.append('message', message);
                
                const response = await fetch('../../controller/MailingController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast(`Email envoyé avec succès! ${data.data.success} réussis, ${data.data.failed} échoués`);
                    addToEmailHistory(subject, selectedRecipients.length, data.data.success);
                } else {
                    showToast('Erreur lors de l\'envoi', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Erreur réseau', 'error');
            }
        }
        
        function previewEmail() {
            const subject = document.getElementById('emailSubject').value;
            const message = document.getElementById('emailMessage').value;
            
            if (!subject || !message) {
                showToast('Veuillez remplir le sujet et le message', 'error');
                return;
            }
            
            // Simple preview
            const preview = `
                <div style="background: linear-gradient(135deg, #00A651 0%, #008040 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h1>DigiWork HUB</h1>
                </div>
                <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
                    <h2>${subject}</h2>
                    <div style="white-space: pre-wrap;">${message}</div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = preview;
            document.getElementById('previewModal').style.display = 'block';
        }
        
        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }
        
        function addToEmailHistory(subject, count, success) {
            const tbody = document.getElementById('emailHistory');
            const now = new Date().toLocaleString('fr-FR');
            
            const row = `
                <tr>
                    <td>${now}</td>
                    <td>${subject}</td>
                    <td>${count}</td>
                    <td><span class="status-badge status-actif">${success} envoyés</span></td>
                </tr>
            `;
            
            if (tbody.querySelector('td[colspan]')) {
                tbody.innerHTML = row;
            } else {
                tbody.insertAdjacentHTML('afterbegin', row);
            }
        }
    </script>
</body>
</html>
