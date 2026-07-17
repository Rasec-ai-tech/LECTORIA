<?php
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/Livre.php';

$estConnecte = AuthService::estConnecte();
$livresMisEnAvant = Livre::findFeatured(6);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LECTORIA — La bibliothèque en ligne qui connecte lecteurs et collections</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/components.css">
<script src="assets/js/nav.js" defer></script>
<script src="assets/js/recherche.js" defer></script>
</head>
<body>

<nav class="navbar">
  <div class="wrap">
    <a href="index.php" class="brand">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
      LECTORIA
    </a>
    <ul class="nav-links">
      <li><a href="index.php" class="active">Accueil</a></li>
      <li><a href="results.php">Catalogue</a></li>
      <?php if ($estConnecte): ?>
        <li><a href="wishlist.php">Ma liste de lecture</a></li>
        <li><a href="logout.php" class="btn-login">Déconnexion</a></li>
      <?php else: ?>
        <li><a href="wishlist.php">Ma liste</a></li>
        <li><a href="login.php" class="btn-login">Connexion</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<header class="hero">
  <div class="wrap">
    <h1>La bibliothèque en ligne qui <em>connecte lecteurs et collections</em></h1>
    <p>Recherchez, réservez et suivez vos lectures depuis un seul endroit, pensé pour les bibliothèques modernes.</p>

    <form class="search-panel" action="results.php" method="get">
      <label for="search-scope" class="sr-only">Sélection du champ de recherche</label>
      <select id="search-scope" class="search-select" name="scope">
        <option value="titre">Titre</option>
        <option value="auteur">Auteur</option>
        <option value="les-deux">Les deux</option>
      </select>
      <div class="search-input-group">
        <label for="search-input" class="sr-only">Recherche</label>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input id="search-input" type="text" name="q" placeholder="Rechercher un titre, un auteur…" aria-label="Recherche">
      </div>
      <button type="submit">Rechercher</button>
    </form>
  </div>
</header>

<section class="featured">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>Livres mis en avant</h2>
        <p>Une sélection actualisée chaque semaine</p>
      </div>
      <a href="results.php" class="see-all">Voir tout le catalogue →</a>
    </div>
    <div class="book-grid">
      <?php foreach ($livresMisEnAvant as $livre): ?>
        <article class="book-card">
          <div class="cover-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
          <div class="book-body">
            <h3><?= htmlspecialchars((string) ($livre['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string) ($livre['auteur'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<footer>
  <p>© 2026 LECTORIA · By IMIEN Jean César Le Grand · <a href="login.php?redirect=admin/dashboard.php">Accès administrateur</a></p>
</footer>

</body>
</html>
