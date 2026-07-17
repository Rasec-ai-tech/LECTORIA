<?php
/**
 * Partiel de formulaire commun pour l'ajout et la modification de livres.
 */

$donnees = $donnees ?? [];
$errors = $errors ?? [];
$submitLabel = $submitLabel ?? 'Publier le livre dans la collection';
$action = $action ?? 'add_livre.php';
$csrfToken = $csrfToken ?? Csrf::genererToken();
$livreId = isset($donnees['id']) ? (int) $donnees['id'] : 0;
?>
<form class="form-card" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($livreId > 0): ?>
        <input type="hidden" name="id" value="<?= (int) $livreId ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="field full">
            <label for="titre">Titre de l'ouvrage</label>
            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars((string) ($donnees['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
            <?php if (!empty($errors['titre'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="auteur">Auteur</label>
            <input type="text" id="auteur" name="auteur" value="<?= htmlspecialchars((string) ($donnees['auteur'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
            <?php if (!empty($errors['auteur'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['auteur'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="maison_edition">Maison d'édition</label>
            <input type="text" id="maison_edition" name="maison_edition" value="<?= htmlspecialchars((string) ($donnees['maison_edition'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="255">
            <?php if (!empty($errors['maison_edition'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['maison_edition'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="nombre_exemplaire">Nombre d'exemplaires <span class="hint">(quantité en stock)</span></label>
            <input type="number" id="nombre_exemplaire" name="nombre_exemplaire" min="0" value="<?= htmlspecialchars((string) ($donnees['nombre_exemplaire'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (!empty($errors['nombre_exemplaire'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['nombre_exemplaire'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="field full">
            <label for="description">Description / Synopsis</label>
            <textarea id="description" name="description" maxlength="2000" placeholder="Résumez l'intrigue ou le contenu de l'ouvrage…"><?= htmlspecialchars((string) ($donnees['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (!empty($errors['description'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-publish"><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?></button>
        <a href="dashboard.php" class="btn-cancel">Annuler</a>
    </div>
</form>
