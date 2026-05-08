<?php
require_once __DIR__ . '/../../../controller/projectController.php';

$projetC = new ProjetC();
$projects = $projetC->listProjets();

$staticUser = [
    "instagram" => "jeddey.yassine"
];

$images = [
    "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80",
    "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80",
    "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80",
    "https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80",
    "https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=1200&q=80",
    "https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1200&q=80"
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Explorer les Projets — DigiWork Hub</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background: #f3f4f6; color: #1f2d3d; }
    a { text-decoration: none; color: inherit; }
    .container { width: 84%; max-width: 1300px; margin: auto; }

    .navbar { background: #ffffff; padding: 16px 0; border-bottom: 1px solid #e6e6e6; }
    .navbar-content { display: flex; justify-content: space-between; align-items: center; }
    .nav-links { display: flex; align-items: center; gap: 34px; font-size: 15px; font-weight: 600; color: #30435d; }
    .nav-links a { position: relative; padding-bottom: 8px; transition: 0.2s; }
    .nav-links a.active { color: #1f5fd1; }
    .nav-links a.active::after { content: ""; position: absolute; left: 0; bottom: -10px; width: 100%; height: 4px; border-radius: 10px; background: #2d73ea; }

    .logo { display: flex; align-items: center; gap: 6px; }
    .logo img { height: 40px; width: auto; object-fit: contain; }
    .logo-text { font-size: 22px; font-weight: 500; letter-spacing: 0.2px; color: #1b3f8b; display: flex; align-items: center; }
    .logo-text .hub { color: #2ecc71; margin-left: 2px; font-weight: 600; }

    .hero { position: relative; background: linear-gradient(135deg, #145cbc 0%, #1f4fb6 55%, #294cae 100%); color: white; padding: 70px 20px 56px; overflow: hidden; text-align: center; }
    .hero h1 { position: relative; z-index: 2; font-size: 30px; font-weight: 800; margin-bottom: 16px; }
    .hero p { position: relative; z-index: 2; font-size: 16px; opacity: 0.95; }

    .section { padding: 34px 0 30px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px; }
    .section-title { font-size: 20px; font-weight: 800; color: #243b63; }
    .see-more { font-size: 13px; color: #8b97a8; background: #eef0f3; padding: 9px 16px; border-radius: 20px; font-weight: 700; }

    .card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .card { background: #fff; border-radius: 14px; overflow: visible; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: 1px solid #e8e8e8; }
    .card img { width: 100%; height: 180px; object-fit: cover; display: block; border-radius: 14px 14px 0 0; }
    .card-body { padding: 16px; }
    .card h3 { font-size: 17px; font-weight: 800; color: #24344d; margin-bottom: 10px; }
    .description { font-size: 13px; color: #607089; line-height: 1.4; margin-bottom: 14px; min-height: 38px; }
    .status { display: inline-block; background: #eef6ff; color: #0c73b7; padding: 6px 10px; border-radius: 20px; font-size: 12px; font-weight: 800; margin-bottom: 14px; }

    .card-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .price { color: #49ab69; font-size: 26px; font-weight: 800; }
    .price small { font-size: 16px; margin-right: 4px; }

    .mini-btn { background: #0c73b7; color: white; border: none; border-radius: 8px; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; display: inline-block; }
    .mini-btn:hover { background: #095f96; }

    .card-actions { display: flex; gap: 8px; align-items: center; }
    .share-wrapper { position: relative; display: inline-block; }
    .share-btn { background: #26a9e0; color: white; border: none; border-radius: 8px; padding: 10px 13px; font-weight: 700; font-size: 13px; cursor: pointer; }
    .share-menu { display: none; position: absolute; right: 0; bottom: 45px; background: white; min-width: 155px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.18); z-index: 999; overflow: hidden; }
    .share-menu a { display: block; padding: 11px 14px; color: #24344d; font-size: 13px; font-weight: 700; background: white; }
    .share-menu a:hover { background: #f1f5f9; }
    .share-wrapper:hover .share-menu { display: block; }

    @media (max-width: 1100px) { .card-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
      .container { width: 92%; }
      .navbar-content { flex-direction: column; gap: 16px; }
      .nav-links { flex-wrap: wrap; justify-content: center; gap: 18px; }
      .card-grid { grid-template-columns: 1fr; }
      .section-header { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>

<body>

<header class="navbar">
  <div class="container navbar-content">
    <div class="logo">
      <img src="/projectttttttt/assets/img/logo/logo.png" alt="logo">
      <span class="logo-text">DigiWork<span class="hub">HUB</span></span>
    </div>

    <nav class="nav-links">
      <a href="/projectttttttt/index.php">Accueil</a>
      <a href="/projectttttttt/index.php?page=projets">Projets</a>
      <a href="/projectttttttt/index.php?page=explore" class="active">Explorer</a>
      <a href="/projectttttttt/index.php?page=events">Événements</a>
      <a href="/projectttttttt/index.php?page=packs">Packs</a>
    </nav>
  </div>
</header>

<section class="hero">
  <div class="container">
    <h1>Explorer tous les projets</h1>
    <p>Découvrez tous les projets disponibles sur DigiWorkHub.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Tous les Projets</h2>
      <a href="/projectttttttt/index.php?page=projets" class="see-more">Retour Projets</a>
    </div>

    <div class="card-grid">
      <?php
      $i = 0;
      foreach ($projects as $project):
        $image = $images[$i % count($images)];
        $title = htmlspecialchars($project['titre']);
        $description = htmlspecialchars($project['discription']);
        $budget = number_format((float)$project['budget'], 2);
        $status = htmlspecialchars($project['statut']);

        $shareUrlRaw = "http://localhost/projectttttttt/index.php?page=explore&projectId=" . $project['id-projet'];
        $shareUrl = urlencode($shareUrlRaw);

        $shareText = urlencode(
          "🚀 Projet: " . $project['titre'] .
          " | 💰 $" . $budget .
          "\nVoir ici 👉 " . $shareUrlRaw
        );
      ?>

      <div class="card">
        <img src="<?= $image ?>" alt="<?= $title ?>">

        <div class="card-body">
          <h3><?= $title ?></h3>
          <p class="description"><?= $description ?></p>
          <span class="status"><?= $status ?></span>

          <div class="card-footer">
            <div class="price"><small>$</small><?= $budget ?></div>

            <div class="card-actions">
              <a href="/projectttttttt/index.php?page=explore&projectId=<?= $project['id-projet'] ?>" class="mini-btn">
                Voir Projet
              </a>

              <div class="share-wrapper">
                <button class="share-btn">Share</button>

                <div class="share-menu">
                  <a target="_blank" href="https://web.whatsapp.com/send?text=<?= $shareText ?>">WhatsApp</a>
                  <a target="_blank"
   href="https://www.facebook.com/messages/"
   onclick="copyLink('<?= htmlspecialchars($shareUrlRaw, ENT_QUOTES) ?>')">
   Facebook
</a>
                  <a target="_blank"href="https://www.instagram.com/direct/inbox/"onclick="copyLink('<?= htmlspecialchars($shareUrlRaw, ENT_QUOTES) ?>')">Instagram</a>
                  <a href="mailto:?subject=Projet DigiWorkHub&body=<?= $shareText ?>">Email</a>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <?php
      $i++;
      endforeach;
      ?>
    </div>
  </div>
</section>

<script>
function copyLink(url) {
  navigator.clipboard.writeText(url);
  alert("Link copied! Paste it in Instagram DM.");
}
</script>

</body>
</html>