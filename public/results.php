<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Models/Livre.php';

$terme = Validation::nettoyerTexte($_GET['q'] ?? '');
$page = Validation::validerEntier($_GET['page'] ?? 1) ?? 1;
$limite = 12;
$offset = ($page - 1) * $limite;
$ajaxMode = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if ($terme === '') {
    $count = Livre::countAll();
    $livres = Livre::findAll($limite, $offset);
} else {
    $count = Livre::countSearch($terme);
    $livres = Livre::search($terme, $limite, $offset);
}

$hasResults = $count > 0;

if ($ajaxMode) {
    header('X-Has-More: ' . (($page * $limite) < $count ? '1' : '0'));
    require __DIR__ . '/partials/results_grid.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $terme === '' ? 'Catalogue' : 'Résultats de recherche' ?> — LECTORIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/components.css">
<script src="assets/js/nav.js" defer></script>
<script src="assets/js/resultats.js" defer></script>
</head>
<body>

<nav class="navbar">
  <div class="wrap">
    <a href="index.php" class="brand"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>LECTORIA</a>
    <ul class="nav-links">
      <li><a href="index.php">Accueil</a></li>
      <li><a href="results.php" class="active">Catalogue</a></li>
      <li><a href="wishlist.php">Ma liste</a></li>
      <li><a href="login.php" class="btn-login">Connexion</a></li>
    </ul>
  </div>
</nav>

<section class="results-header">
  <div class="wrap">
    <p class="breadcrumb"><a href="index.php">Accueil</a> / <?= $terme === '' ? 'Catalogue' : 'Résultats de recherche' ?></p>
    <h1><?= $terme === '' ? 'Catalogue' : 'Résultats de recherche' ?></h1>
    <p class="count">
      <strong><?= (int) $count ?> livre<?= $count > 1 ? 's' : '' ?></strong>
      <?= $terme === '' ? 'affiché' . ($count > 1 ? 's' : '') : 'trouvé' . ($count > 1 ? 's' : '') ?>
      <?= $terme !== '' ? ' pour « ' . htmlspecialchars($terme, ENT_QUOTES, 'UTF-8') . ' »' : '' ?>
    </p>
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
        <input id="search-input" type="text" name="q" value="<?= htmlspecialchars($terme, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher un titre, un auteur…" aria-label="Recherche">
      </div>
      <button type="submit">Rechercher</button>
    </form>
  </div>
</section>

<section class="wrap">
  <?php if (!$hasResults): ?>
    <div class="empty-state">
      <p>Aucun résultat trouvé pour « <?= htmlspecialchars($terme, ENT_QUOTES, 'UTF-8') ?> ».</p>
      <a href="results.php" class="btn-view">Retour au catalogue</a>
    </div>
  <?php else: ?>
    <?php
    $livres = array_slice($livres, 0, $limite);
    require __DIR__ . '/partials/results_grid.php';
    ?>
    <div class="pagination" data-page="<?= (int) $page ?>">
      <?php
      $prevPage = max(1, $page - 1);
      $nextPage = $page + 1;
      $hasPrev = $page > 1;
      $hasNext = ($page * $limite) < $count;
      ?>
      <?php if ($hasPrev): ?>
        <a href="results.php?q=<?= urlencode($terme) ?>&page=<?= (int) $prevPage ?>" class="btn-view">Précédent</a>
      <?php endif; ?>
      <?php if ($hasNext): ?>
        <a href="results.php?q=<?= urlencode($terme) ?>&page=<?= (int) $nextPage ?>" class="btn-view">Suivant</a>
      <?php endif; ?>
      <?php if ($hasNext): ?>
        <button type="button" id="load-more-button" class="btn-view" data-page="<?= (int) ($page + 1) ?>" data-has-next="1">Charger plus</button>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

</body>
</html>
