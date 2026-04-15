<?php
session_start();
require_once("../../model/Pack.php");
$packModel = new Pack();
$packs = $packModel->getAll();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html class="no-js" lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>DigiWork Hub</title>
    <meta name="description" content="Plateforme pour entrepreneurs digitaux" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="assets/css/tiny-slider.css"/>
    <link rel="stylesheet" href="assets/css/animate.css"/>
    <link rel="stylesheet" href="assets/css/lindy-uikit.css"/>
  <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .toast-notification { position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 15px 20px; border-radius: 8px; color: white; font-weight: bold; animation: slideIn 0.3s ease; }
        .toast-success { background: #10B981; }
        .toast-error { background: #EF4444; }
        .toast-warning { background: #F59E0B; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .login-prompt { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 10000; }
        .login-modal { background: white; padding: 30px; border-radius: 16px; text-align: center; max-width: 400px; }
    </style>
  </head>
  <body>
    <section id="home" class="hero-section-wrapper-5">
      <header class="header header-6">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logo/logo.svg" alt="Logo" />
                  </a>
                  <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent6">
                    <?php
                    session_start();
                    require_once("../../model/Pack.php");
                    $packModel = new Pack();
                    $packs = $packModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
                    $isLoggedIn = isset($_SESSION['user_id']);
                    ?>
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                      <meta charset="UTF-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <title>DigiWork HUB - Plateforme Freelance Durable</title>
                      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                      <link rel="stylesheet" href="assets/css/style.css">
                      <style>
                        .toast {
                          position: fixed;
                          bottom: 20px;
                          right: 20px;
                          background: var(--primary);
                          color: white;
                          padding: 12px 24px;
                          border-radius: 12px;
                          z-index: 1000;
                          animation: slideIn 0.3s ease;
                        }
                        @keyframes slideIn {
                          from { transform: translateX(100%); opacity: 0; }
                          to { transform: translateX(0); opacity: 1; }
                        }
                      </style>
                    </head>
                    <body>
                      <div class="app-container">
                        <!-- Sidebar -->
                        <aside class="sidebar">
                          <div class="sidebar-logo">
                            <img src="assets/img/logo/logo.png" alt="DigiWork HUB" onerror="this.src='https://via.placeholder.com/120x40?text=DigiWork+HUB'">
                            <h2>DigiWork <span>HUB</span></h2>
                          </div>
                          <nav class="sidebar-menu">
                            <a href="#" class="sidebar-item active">
                              <i class="fas fa-tachometer-alt"></i>
                              <span>Tableaux de Bord</span>
                            </a>
                            <a href="#" class="sidebar-item">
                              <i class="fas fa-users"></i>
                              <span>Gestion Utilisateurs</span>
                            </a>
                            <a href="mes_abonnements.php" class="sidebar-item">
                              <i class="fas fa-briefcase"></i>
                              <span>Projets & Offres</span>
                            </a>
                            <a href="#" class="sidebar-item">
                              <i class="fas fa-robot"></i>
                              <span>IA Matching</span>
                            </a>
                            <a href="#" class="sidebar-item">
                              <i class="fas fa-star"></i>
                              <span>Évaluations</span>
                            </a>
                            <a href="#" class="sidebar-item">
                              <i class="fas fa-leaf"></i>
                              <span>Durabilité</span>
                            </a>
                          </nav>
                        </aside>

                        <!-- Main Content -->
                        <main class="main-content">
                          <!-- Top Bar -->
                          <header class="top-bar">
                            <div class="search-bar">
                              <i class="fas fa-search"></i>
                              <input type="text" placeholder="Rechercher...">
                            </div>
                            <div class="user-menu">
                              <i class="fas fa-bell"></i>
                              <i class="fas fa-envelope"></i>
                              <div class="user-avatar">
                                <i class="fas fa-user"></i>
                              </div>
                              <?php if($isLoggedIn): ?>
                                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                <a href="../../controller/AuthController.php?action=logout" style="color: var(--danger);">Déconnexion</a>
                              <?php else: ?>
                                <a href="login.php" class="btn-primary" style="padding: 8px 20px;">Connexion</a>
                              <?php endif; ?>
                            </div>
                          </header>

                          <!-- Content Area -->
                          <div class="content-area">
                            <!-- Hero Section -->
                            <div class="hero-section">
                              <h1>Trouvez les meilleures opportunités freelances pour vous!</h1>
                              <p>Boostez votre carrière digitale avec l'IA & le développement durable.</p>
                              <button class="btn-primary">Explore les Projets →</button>
                            </div>

                            <!-- Stats Grid -->
                            <div class="stats-grid">
                              <div class="stat-card">
                                <div class="stat-info">
                                  <h4>Projets Actifs</h4>
                                  <div class="stat-value">1,284</div>
                                </div>
                                <div class="stat-icon">
                                  <i class="fas fa-chart-line"></i>
                                </div>
                              </div>
                              <div class="stat-card">
                                <div class="stat-info">
                                  <h4>Freelances</h4>
                                  <div class="stat-value">3,642</div>
                                </div>
                                <div class="stat-icon">
                                  <i class="fas fa-user-friends"></i>
                                </div>
                              </div>
                              <div class="stat-card">
                                <div class="stat-info">
                                  <h4>Taux de Succès</h4>
                                  <div class="stat-value">94%</div>
                                </div>
                                <div class="stat-icon">
                                  <i class="fas fa-trophy"></i>
                                </div>
                              </div>
                              <div class="stat-card">
                                <div class="stat-info">
                                  <h4>Score Durabilité</h4>
                                  <div class="stat-value">82</div>
                                </div>
                                <div class="stat-icon">
                                  <i class="fas fa-leaf"></i>
                                </div>
                              </div>
                            </div>

                            <!-- Your Recommendations -->
                            <div class="section-title">
                              <h3>Your Recommendations</h3>
                              <a href="#" class="section-link">Voir tout →</a>
                            </div>
                            <div class="recommendations-grid">
                              <div class="recommendation-card">
                                <div class="recommendation-icon">
                                  <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="recommendation-info">
                                  <h4>Création de Site e-Commerce</h4>
                                  <p>Basé sur votre profil • 12 missions disponibles</p>
                                </div>
                              </div>
                              <div class="recommendation-card">
                                <div class="recommendation-icon">
                                  <i class="fas fa-video"></i>
                                </div>
                                <div class="recommendation-info">
                                  <h4>Montage Video YouTube</h4>
                                  <p>Très demandé • 8 missions disponibles</p>
                                </div>
                              </div>
                              <div class="recommendation-card">
                                <div class="recommendation-icon">
                                  <i class="fas fa-chart-simple"></i>
                                </div>
                                <div class="recommendation-info">
                                  <h4>Marketing Digital</h4>
                                  <p>+45% de croissance • 15 missions</p>
                                </div>
                              </div>
                            </div>

                            <!-- Two Column Layout -->
                            <div class="two-columns">
                              <!-- Left: Missions Recommandées -->
                              <div>
                                <div class="section-title">
                                  <h3>Missions Recommandées pour Vous</h3>
                                </div>
                                <div class="cards-grid" style="grid-template-columns: 1fr;">
                                  <div class="card">
                                    <div class="card-header">
                                      <span class="card-badge">Urgent</span>
                                      <i class="fas fa-bookmark"></i>
                                    </div>
                                    <h4 class="card-title">Création de Site e-Commerce</h4>
                                    <p class="card-description">Site Shopify complet avec optimisation SEO et paiement intégré.</p>
                                    <div class="card-footer">
                                      <span class="card-price">1,500 dt</span>
                                      <button class="btn-primary" style="padding: 8px 16px;">Postuler</button>
                                    </div>
                                  </div>
                                  <div class="card">
                                    <div class="card-header">
                                      <span class="card-badge">Recommandé</span>
                                      <i class="fas fa-bookmark"></i>
                                    </div>
                                    <h4 class="card-title">Montage Video YouTube</h4>
                                    <p class="card-description">Montage professionnel pour chaîne YouTube (10-15 min/vidéo).</p>
                                    <div class="card-footer">
                                      <span class="card-price">800 dt</span>
                                      <button class="btn-primary" style="padding: 8px 16px;">Postuler</button>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <!-- Right: Formations Conseillées -->
                              <div>
                                <div class="section-title">
                                  <h3>Formations Conseillées</h3>
                                </div>
                                <div class="training-card">
                                  <div class="training-info">
                                    <h4>Cours en Marketing Digital</h4>
                                    <p>Par DigitalPro • 12 heures</p>
                                  </div>
                                  <button class="btn-primary" style="padding: 8px 16px;">S'inscrire</button>
                                </div>
                                <div class="training-card">
                                  <div class="training-info">
                                    <h4>Certification Green IT</h4>
                                    <p>Par EcoDigital • 8 heures</p>
                                  </div>
                                  <button class="btn-primary" style="padding: 8px 16px;">S'inscrire</button>
                                </div>
                                <div class="training-card">
                                  <div class="training-info">
                                    <h4>IA pour Freelances</h4>
                                    <p>Par AI Academy • 15 heures</p>
                                  </div>
                                  <button class="btn-primary" style="padding: 8px 16px;">S'inscrire</button>
                                </div>
                              </div>
                            </div>

                            <!-- Projets Récents -->
                            <div class="section-title">
                              <h3>Projets Récents</h3>
                              <a href="#" class="section-link">Voir tout →</a>
                            </div>
                            <div class="cards-grid">
                              <div class="card">
                                <div class="card-header">
                                  <span class="card-badge">Développement</span>
                                </div>
                                <h4 class="card-title">Développement Application Mobile</h4>
                                <p class="card-description">Application React Native pour startup fintech.</p>
                                <div class="card-footer">
                                  <span class="card-price">3,200 dt</span>
                                  <button class="btn-primary" style="padding: 8px 16px;">Voir</button>
                                </div>
                              </div>
                              <div class="card">
                                <div class="card-header">
                                  <span class="card-badge">Vidéo</span>
                                </div>
                                <h4 class="card-title">Vidéo Promotionnelle</h4>
                                <p class="card-description">Vidéo corporate de 2 minutes pour lancement produit.</p>
                                <div class="card-footer">
                                  <span class="card-price">1,200 dt</span>
                                  <button class="btn-primary" style="padding: 8px 16px;">Voir</button>
                                </div>
                              </div>
                              <div class="card">
                                <div class="card-header">
                                  <span class="card-badge">SEO</span>
                                </div>
                                <h4 class="card-title">Audit SEO Durable</h4>
                                <p class="card-description">Audit complet avec recommandations éco-responsables.</p>
                                <div class="card-footer">
                                  <span class="card-price">950 dt</span>
                                  <button class="btn-primary" style="padding: 8px 16px;">Voir</button>
                                </div>
                              </div>
                            </div>

                            <!-- Packs Section -->
                            <div class="section-title">
                              <h3>Nos Packs d'Abonnement</h3>
                            </div>
                            <div class="cards-grid">
                              <?php foreach($packs as $pack): ?>
                              <div class="card">
                                <div class="card-header">
                                  <span class="card-badge">Pack</span>
                                </div>
                                <h4 class="card-title"><?= htmlspecialchars($pack['nom-pack']) ?></h4>
                                <p class="card-description"><?= htmlspecialchars(substr($pack['description'], 0, 80)) ?>...</p>
                                <div class="card-footer">
                                  <span class="card-price"><?= htmlspecialchars($pack['prix']) ?> dt</span>
                                  <button class="btn-primary" style="padding: 8px 16px;" onclick="handleSubscribe(<?= $pack['id-pack'] ?>)">S'abonner</button>
                                </div>
                              </div>
                              <?php endforeach; ?>
                            </div>
                          </div>
                        </main>
                      </div>

                      <script>
                        const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
        
                        function showToast(message) {
                          const toast = document.createElement('div');
                          toast.className = 'toast';
                          toast.innerHTML = message;
                          document.body.appendChild(toast);
                          setTimeout(() => toast.remove(), 3000);
                        }
        
                        async function handleSubscribe(packId) {
                          if (!isLoggedIn) {
                            showToast('Veuillez vous connecter pour vous abonner');
                            setTimeout(() => window.location.href = 'login.php', 1500);
                            return;
                          }
            
                          const confirmed = confirm('Confirmez-vous votre abonnement ?');
                          if (!confirmed) return;
            
                          const formData = new FormData();
                          formData.append('action', 'subscribe');
                          formData.append('pack_id', packId);
                          formData.append('ajax', '1');
            
                          try {
                            const response = await fetch('../../controller/AbonnementController.php', {
                              method: 'POST',
                              body: formData
                            });
                            const data = await response.json();
                            if (data.status === 'success') {
                              showToast('Abonnement créé avec succès !');
                              setTimeout(() => window.location.href = 'mes_abonnements.php', 1500);
                            } else {
                              showToast(data.message);
                            }
                          } catch (error) {
                            showToast('Erreur réseau');
                          }
                        }
                      </script>
                    </body>
                    </html>
            modal.setAttribute('aria-hidden', 'true');
        }

        document.getElementById('loginPrompt').addEventListener('click', function(e) {
            if (e.target === this) closeLoginPrompt();
        });

        // keyboard: Esc to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('loginPrompt');
                if (modal && modal.style.display === 'flex') closeLoginPrompt();
            }
        });

        document.getElementById('loginNowBtn').addEventListener('click', function() {
            window.location.href = 'login.php';
        });
        document.getElementById('closeLoginBtn').addEventListener('click', closeLoginPrompt);

        // Attach event listeners to subscribe buttons
        document.querySelectorAll('.subscribe-btn').forEach(btn => {
            btn.addEventListener('click', () => handleSubscribe(btn.dataset.packId, btn));
        });

        async function handleSubscribe(packId, btnElement) {
            // Validation #1: Check if user is logged in
            if (!isLoggedIn) {
                showLoginPrompt();
                return;
            }

            // Confirm subscription
            const confirmed = confirm('Êtes-vous sûr de vouloir vous abonner à ce pack ?');
            if (!confirmed) return;

            // Defensive: ensure button exists
            const btn = btnElement || document.querySelector(`.subscribe-btn[data-pack-id="${packId}"]`);
            if (!btn) { showToast('Impossible de trouver le bouton. Rafraîchissez la page.', 'error'); return; }

            const originalText = btn.textContent;
            btn.textContent = 'Chargement...';
            btn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('action', 'subscribe');
                formData.append('pack_id', packId);
                formData.append('ajax', '1');

                const response = await fetch('../../controller/AbonnementController.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                // If server returned non-2xx, try to parse JSON for message; otherwise show generic error
                let data = null;
                const text = await response.text();
                try {
                    data = text ? JSON.parse(text) : null;
                } catch (e) {
                    // Non-JSON response
                    data = null;
                }

                if (!response.ok) {
                    // Handle unauthorized explicitly
                    if (response.status === 401 || (data && data.message && /connect/i.test(data.message))) {
                        showLoginPrompt();
                        btn.textContent = originalText;
                        btn.disabled = false;
                        return;
                    }

                    showToast((data && data.message) ? data.message : 'Erreur serveur. Veuillez réessayer.', 'error');
                    btn.textContent = originalText;
                    btn.disabled = false;
                    return;
                }

                // Successful 200-series response
                if (data && data.status === 'success') {
                    showToast('Abonnement créé avec succès !', 'success');
                    setTimeout(() => { window.location.href = 'mes_abonnements.php'; }, 1200);
                    return;
                }

                // Fallback: unknown payload
                showToast((data && data.message) ? data.message : 'Erreur lors de l\'abonnement', 'error');
                btn.textContent = originalText;
                btn.disabled = false;

            } catch (error) {
                console.error('Error:', error);
                showToast('Erreur réseau. Veuillez réessayer.', 'error');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        }
    </script>
  </body>
</html>
