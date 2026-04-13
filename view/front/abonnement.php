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
    <div class="front-navbar">
        <div class="logo-container">
            <img src="../frontoffice/assets/img/logo/digiwork-hub.png" alt="DigiWork HUB" style="height:48px;">
        </div>
        <div class="nav-links">
            <a href="../frontoffice/index.php">Accueil</a>
            <a href="#">Projets</a>
            <a href="packs.php">Packs & Formations</a>
            <a href="#">Durabilité</a>
            <a href="abonnement.php" style="color: var(--primary);">Mon Profil</a>
        </div>
    </div>

    <div class="profile-container">
        <h2 style="color: var(--primary);">Mes Abonnements Actifs</h2>
        
        <div id="abonnements-container">
            <!-- Data loaded via Fetch -->
            <p style="color: var(--text-muted);">Chargement de vos abonnements...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../controller/AbonnementController.php?action=getAll')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('abonnements-container');
                
                // Pretend we only want to see user #1 since it's the mocked user
                const myAbo = data.filter(d => d['id-user'] == 1);
                
                if (myAbo.length === 0) {
                    container.innerHTML = `<p>Vous n'avez aucun abonnement actif. <a href="packs.php">Découvrir nos offres</a></p>`;
                    return;
                }

                let html = '';
                myAbo.forEach(abo => {
                    html += `
                        <div class="list-item">
                            <div class="list-info">
                                <h3>Pack : ${abo['nom-pack']}</h3>
                                <p style="font-size: 14px; color: var(--text-muted); margin:0;">
                                    <strong>Du :</strong> ${abo['date-deb']} <br>
                                    <strong>Au :</strong> ${abo['date-fin']}
                                </p>
                            </div>
                            <div>
                                <span style="background: #D1FAE5; color: #065F46; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">${abo.status}</span>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('abonnements-container').innerHTML = `<p style="color:red">Erreur de chargement.</p>`;
            });
        });
    </script>
</body>
</html>
