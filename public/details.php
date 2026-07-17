<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Models/Livre.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

function afficherErreur404(): void
{
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>404 — LECTORIA</title></head><body><p>Page introuvable.</p></body></html>';
    exit;
}

$id = Validation::validerEntier($_GET['id'] ?? null);
if ($id === null) {
    afficherErreur404();
}

$livre = Livre::findById($id);
if ($livre === null) {
    afficherErreur404();
}

$estConnecte = AuthService::estConnecte();
$stock = (int) ($livre['nombre_exemplaire'] ?? 0);
$csrfToken = Csrf::genererToken();
$suggestions = Livre::findByAuteur((string) ($livre['auteur'] ?? ''), $id, 3);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars((string) ($livre['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) ($livre['auteur'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — LECTORIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/components.css">
<script src="assets/js/nav.js" defer></script>
<script src="assets/js/wishlist.js" defer></script>
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

<div class="wrap breadcrumb">
  <a href="index.php">Accueil</a> / <a href="results.php">Catalogue</a> / <?= htmlspecialchars((string) ($livre['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<section class="wrap details">

  <div>
    <div class="cover-placeholder">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
    </div>
    <div class="meta-card">
      <h4>Détails de l'édition</h4>
      <div class="meta-row"><span class="k">Éditeur</span><span class="v"><?= htmlspecialchars((string) ($livre['maison_edition'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="meta-row"><span class="k">Stock</span><span class="v stock"><?= (int) $stock ?> exemplaire<?= $stock > 1 ? 's' : '' ?> disponible<?= $stock > 1 ? 's' : '' ?></span></div>
    </div>
  </div>

  <div>
    <span class="cat-eyebrow">Science-fiction · Cycle de Fondation</span>
    <h1 class="title"><?= htmlspecialchars((string) ($livre['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="author">Par <strong><?= htmlspecialchars((string) ($livre['auteur'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>

    <div class="description">
      <p><?= nl2br(htmlspecialchars((string) ($livre['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    </div>

    <?php if ($estConnecte && $stock > 0): ?>
      <form method="post" action="add_to_wishlist.php">
        <input type="hidden" name="id_livre" value="<?= (int) $id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn-cta">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
          Ajouter à ma liste de lecture
        </button>
      </form>
    <?php elseif (!$estConnecte): ?>
      <p>Connectez-vous pour emprunter ce livre. <a href="login.php?redirect=<?= urlencode('/public/details.php?id=' . $id) ?>">Connexion</a></p>
    <?php else: ?>
      <p>Aucun exemplaire disponible.</p>
    <?php endif; ?>
  </div>

</section>

<?php if (!empty($suggestions)): ?>
  <section class="wrap suggestions">
    <h2>Suggestions</h2>
    <div class="results-grid">
      <?php foreach ($suggestions as $suggestion): ?>
        <article class="book-card">
          <div class="cover-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
          <div class="book-body">
            <div>
              <h3><?= htmlspecialchars((string) ($suggestion['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="author"><?= htmlspecialchars((string) ($suggestion['auteur'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="details.php?id=<?= (int) ($suggestion['id'] ?? 0) ?>" class="btn-view">Voir la fiche</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

</body>
</html>
