<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BO - Gestion des Abonnements</title>
    <link rel="stylesheet" href="../style.css">
    <!-- Template assets to match frontoffice look -->
    <link rel="stylesheet" href="../frontoffice/assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="../frontoffice/assets/css/LineIcons.2.0.css">
    <link rel="stylesheet" href="../frontoffice/assets/css/lindy-uikit.css">
</head>
<body>
    <div class="back-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="../frontoffice/assets/img/logo/logo.png" alt="DigiWork HUB" style="height:40px;">
            </div>
            <div class="sidebar-menu">
                <a href="#" class="sidebar-item">
                    <i>📊</i> Tableau de Bord
                </a>
                <a href="#" class="sidebar-item">
                    <i>👥</i> Gestion Utilisateurs
                </a>
                <a href="dashboard_packs.php" class="sidebar-item">
                    <i>💼</i> Projets & Offers (Packs)
                </a>
                <a href="dashboard_abonnements.php" class="sidebar-item active">
                    <i>💳</i> Abonnements
                </a>
            </div>
        </div>
        
        <div class="main-wrapper">
            <div class="topbar">
                <span>Admin | Messages ▾ | Profil ▾</span>
            </div>

            <div class="content">
                <div class="dashboard-panel">
                    <div class="panel-title">Toutes les souscriptions actives</div>
                    <table class="admin-table" id="abo-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Téléphone</th>
                                <th>Pack Associé</th>
                                <th>Période</th>
                                <th>Statut</th>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../controller/AbonnementController.php?action=getAll')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#abo-table tbody');
                let html = '';
                data.forEach(a => {
                    html += `
                        <tr id="abo-${a['id-abonnement']}">
                            <td>${a['id-abonnement']}</td>
                            <td>${a.nom}</td>
                            <td>${a.tel}</td>
                            <td style="font-weight: bold; color: var(--primary);">${a['nom-pack']}</td>
                            <td>${a['date-deb']} au ${a['date-fin']}</td>
                            <td><span style="background: #D1FAE5; color: #065F46; padding: 4px 8px; border-radius: 4px; font-size: 12px;">${a.status}</span></td>
                            <td>
                                <button class="btn-sm" style="background: var(--danger-color);" onclick="deleteAbo(${a['id-abonnement']})">Révoquer</button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            })
            .catch(err => console.error(err));
        });

        function deleteAbo(id) {
            if(!confirm('Êtes-vous sûr de vouloir supprimer cet abonnement ?')) return;

            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);

            fetch('../../controller/AbonnementController.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('abo-' + id).remove();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert("Erreur"));
        }
    </script>
    <script src="../frontoffice/assets/js/bootstrap-5.0.0-beta1.min.js"></script>
    <script src="../frontoffice/assets/js/main.js"></script>
</body>
</html>
