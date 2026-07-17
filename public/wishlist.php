<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/ListeLecture.php';

AuthService::exigerConnexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idLecteur = (int) ($_SESSION['utilisateur_id'] ?? 0);
$liste = ListeLecture::findByLecteur($idLecteur);
$jourAujourdhui = new DateTimeImmutable('now');
$delaiEmprunt = 21;
$compteurTotal = count($liste);
$compteurRetard = 0;

foreach ($liste as &$ligne) {
    $dateEmprunt = new DateTimeImmutable((string) ($ligne['date_emprunt'] ?? 'now'));
    $dateRetour = $ligne['date_retour'] ?? null;

    $ligne['statut'] = 'ongoing';
    if ($dateRetour === null && $jourAujourdhui->diff($dateEmprunt)->days > $delaiEmprunt) {
        $ligne['statut'] = 'late';
        $compteurRetard++;
    }
}
unset($ligne);
$csrfToken = Csrf::genererToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ma liste de lecture — LECTORIA</title>
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
      <li><a href="results.php">Catalogue</a></li>
      <li><a href="wishlist.php" class="active">Ma liste</a></li>
      <li><a href="logout.php" class="btn-login">Déconnexion</a></li>
    </ul>
  </div>
</nav>

<section class="wrap dash-header">
  <h1>Ma liste de lecture</h1>
  <p>Suivez les livres empruntés et réservés depuis votre espace lecteur</p>

  <div class="tracker">
    <span class="item" id="tracker-total" data-count="<?= (int) $compteurTotal ?>"><span class="dot ok"></span><?= (int) $compteurTotal ?> livre<?= $compteurTotal > 1 ? 's' : '' ?> enregistré<?= $compteurTotal > 1 ? 's' : '' ?></span>
    <span class="sep"></span>
    <span class="item" id="tracker-late" data-count="<?= (int) $compteurRetard ?>"><span class="dot late"></span><?= (int) $compteurRetard ?> en retard</span>
  </div>
</section>

<section class="wrap">
  <div class="reading-list">
    <?php foreach ($liste as $ligne): ?>
      <?php
      $idLivre = (int) ($ligne['id_livre'] ?? 0);
      $titre = (string) ($ligne['titre'] ?? '');
      $auteur = (string) ($ligne['auteur'] ?? '');
      $dateEmprunt = (string) ($ligne['date_emprunt'] ?? '');
      $statusLabel = $ligne['statut'] === 'late' ? 'En retard' : 'En cours';
      $statusClass = $ligne['statut'] === 'late' ? 'late' : 'ongoing';
      ?>
      <article class="book-row" data-date-emprunt="<?= htmlspecialchars((string) ($ligne['date_emprunt'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="row-cover"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
        <div class="row-info"><h3><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8') ?></p></div>
        <div class="row-date"><div class="label">Emprunté le</div><div class="value"><?= htmlspecialchars(date('d M Y', strtotime($dateEmprunt)), ENT_QUOTES, 'UTF-8') ?></div></div>
        <span class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
        <form method="post" action="remove_from_wishlist.php">
          <input type="hidden" name="id_livre" value="<?= (int) $idLivre ?>">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn-remove">Retirer de la liste</button>
        </form>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<div id="remove-modal" class="admin-modal hidden" aria-hidden="true">
  <div class="admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="remove-modal-title">
    <h3 id="remove-modal-title">Retirer ce livre ?</h3>
    <p id="remove-modal-message">Voulez-vous vraiment retirer ce livre de votre liste de lecture ?</p>
    <div class="admin-modal-actions">
      <button type="button" id="cancel-remove" class="btn-cancel">Annuler</button>
      <button type="button" id="confirm-remove" class="btn-delete">Confirmer</button>
    </div>
  </div>
</div>

</body>
</html>
