<?php
/**
 * Fragment HTML de la grille des résultats de recherche.
 */
?>
<div class="results-grid">
    <?php foreach ($livres as $livre): ?>
        <?php
        $id = (int) ($livre['id'] ?? 0);
        $titre = (string) ($livre['titre'] ?? '');
        $auteur = (string) ($livre['auteur'] ?? '');
        $description = (string) ($livre['description'] ?? '');
        $descriptionTronquee = trim($description);
        if (strlen($descriptionTronquee) > 120) {
            $descriptionTronquee = rtrim(substr($descriptionTronquee, 0, 120)) . '…';
        }
        $stock = (int) ($livre['nombre_exemplaire'] ?? 0);
        $badgeClass = $stock > 0 ? 'available' : 'out';
        $badgeLabel = $stock > 0 ? $stock . ' exemplaire' . ($stock > 1 ? 's' : '') . ' disponible' . ($stock > 1 ? 's' : '') : 'Épuisé';
        ?>
        <a class="book-card book-card--clickable" href="details.php?id=<?= (int) $id ?>" data-href="details.php?id=<?= (int) $id ?>" aria-label="Voir la fiche de <?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
            <div class="cover-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <div class="book-body">
                <div>
                    <h3><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="author"><?= htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="excerpt"><?= htmlspecialchars($descriptionTronquee, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="btn-view">Voir la fiche</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
