<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../../src/Helpers/Validation.php';
require_once __DIR__ . '/../../src/Helpers/Csrf.php';
require_once __DIR__ . '/../../src/Models/Livre.php';

$id = Validation::validerEntier($_GET['id'] ?? null);
if ($id === null) {
    http_response_code(400);
    exit;
}

$livre = Livre::findById($id);
if ($livre === null) {
    http_response_code(404);
    exit;
}

$donnees = [
    'id' => $id,
    'titre' => (string) ($livre['titre'] ?? ''),
    'auteur' => (string) ($livre['auteur'] ?? ''),
    'maison_edition' => (string) ($livre['maison_edition'] ?? ''),
    'description' => (string) ($livre['description'] ?? ''),
    'nombre_exemplaire' => (int) ($livre['nombre_exemplaire'] ?? 0),
];
$errors = [];
$csrfToken = Csrf::genererToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit;
    }

    $id = Validation::validerEntier($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit;
    }

    $donnees = [
        'id' => $id,
        'titre' => Validation::nettoyerTexte((string) ($_POST['titre'] ?? '')),
        'auteur' => Validation::nettoyerTexte((string) ($_POST['auteur'] ?? '')),
        'maison_edition' => Validation::nettoyerTexte((string) ($_POST['maison_edition'] ?? '')),
        'description' => Validation::nettoyerTexteLong((string) ($_POST['description'] ?? ''), 2000),
        'nombre_exemplaire' => Validation::validerEntier($_POST['nombre_exemplaire'] ?? null),
    ];

    if ($donnees['titre'] === '') {
        $errors['titre'] = 'Le titre est obligatoire.';
    }
    if (mb_strlen($donnees['titre'], 'UTF-8') > 255) {
        $errors['titre'] = 'Le titre ne doit pas dépasser 255 caractères.';
    }

    if ($donnees['auteur'] === '') {
        $errors['auteur'] = 'L\'auteur est obligatoire.';
    }
    if (mb_strlen($donnees['auteur'], 'UTF-8') > 255) {
        $errors['auteur'] = 'L\'auteur ne doit pas dépasser 255 caractères.';
    }

    if ($donnees['maison_edition'] !== '' && mb_strlen($donnees['maison_edition'], 'UTF-8') > 255) {
        $errors['maison_edition'] = 'La maison d\'édition ne doit pas dépasser 255 caractères.';
    }

    if ($donnees['description'] !== '' && mb_strlen($donnees['description'], 'UTF-8') > 2000) {
        $errors['description'] = 'La description ne doit pas dépasser 2000 caractères.';
    }

    if ($donnees['nombre_exemplaire'] === null || $donnees['nombre_exemplaire'] < 0) {
        $errors['nombre_exemplaire'] = 'Le nombre d\'exemplaires doit être un entier positif.';
    }

    if ($errors === []) {
        $miseAJourOk = Livre::mettreAJour($id, $donnees);
        if ($miseAJourOk) {
            $_SESSION['flash_message'] = 'Les modifications du livre ont bien été enregistrées.';
            header('Location: dashboard.php', true, 302);
            exit;
        }

        $errors['global'] = 'Impossible de mettre à jour le livre pour le moment.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier un livre — Admin LECTORIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="../assets/js/admin.js" defer></script>
<style>
  :root{
    --bg:#F8FAFC; --surface:#FFFFFF; --text:#0F172A; --text-muted:#64748B; --text-faint:#94A3B8;
    --accent:#4F46E5; --accent-hover:#4338CA; --accent-light:#EEF2FF;
    --border:#E2E8F0; --danger-bg:#FEF2F2; --danger-text:#B91C1C; --danger-border:#FECACA;
    --radius:12px; --radius-lg:18px;
    --shadow-sm:0 2px 8px rgba(15,23,42,.05);
    --shadow-md:0 10px 24px rgba(15,23,42,.07);
    --shadow-focus:0 0 0 4px rgba(79,70,229,.14);
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Inter',system-ui,-apple-system,sans-serif; background:var(--bg); color:var(--text); line-height:1.5;}
  a{color:inherit; text-decoration:none;}
  ul{list-style:none;}
  button,input,textarea{font-family:inherit;}
  button{cursor:pointer;}
  .admin-shell{display:flex; min-height:100vh;}
  .sidebar{width:250px; flex-shrink:0; background:var(--surface); border-right:1px solid var(--border); padding:24px 18px; display:flex; flex-direction:column; position:sticky; top:0; height:100vh;}
  .brand{display:flex; align-items:center; gap:10px; font-weight:800; font-size:18px; padding:0 8px 26px;}
  .brand-mark{width:32px; height:32px; border-radius:9px; background:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;}
  .brand-mark svg{width:17px; height:17px;}
  .side-label{font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-faint); font-weight:700; padding:0 10px; margin:14px 0 8px;}
  .side-nav{display:flex; flex-direction:column; gap:3px;}
  .side-nav a{display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:9px; font-size:14px; font-weight:500; color:var(--text-muted); transition:.15s ease;}
  .side-nav a svg{width:18px; height:18px; flex-shrink:0;}
  .side-nav a:hover{background:var(--bg); color:var(--text);}
  .side-nav a.active{background:var(--accent-light); color:var(--accent); font-weight:600;}
  .sidebar-footer{margin-top:auto; padding-top:16px; border-top:1px solid var(--border);}
  .admin-chip{display:flex; align-items:center; gap:10px; padding:8px; border-radius:10px;}
  .admin-avatar{width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#4F46E5,#7C3AED); flex-shrink:0;}
  .admin-chip .name{font-size:13.5px; font-weight:600;}
  .admin-chip .role{font-size:11.5px; color:var(--text-faint);}
  .logout-link{display:block; font-size:13px; color:var(--text-muted); padding:8px; margin-top:4px;}
  .logout-link:hover{color:#B91C1C;}
  .main{flex:1; padding:34px 42px; min-width:0;}
  .breadcrumb{font-size:13px; color:var(--text-faint); margin-bottom:10px;}
  .breadcrumb a{color:var(--text-muted); font-weight:500;}
  .breadcrumb a:hover{color:var(--accent);}
  .main-header{display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:26px;}
  .main-header h1{font-size:25px; font-weight:800;}
  .main-header p{font-size:13.5px; color:var(--text-muted); margin-top:5px;}
  .edit-tag{font-size:12px; font-weight:700; color:var(--accent); background:var(--accent-light); padding:6px 12px; border-radius:999px;}
  .form-card{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); padding:32px; max-width:720px;}
  .form-grid{display:grid; grid-template-columns:1fr 1fr; gap:22px 20px;}
  .field{display:flex; flex-direction:column; gap:8px;}
  .field.full{grid-column:1 / -1;}
  .field label{font-size:13.5px; font-weight:600;}
  .field .hint{font-size:12px; color:var(--text-faint); font-weight:400;}
  .field input,.field textarea{border:1px solid var(--border); border-radius:10px; padding:12px 14px; font-size:14.5px; outline:none; background:var(--bg); transition:.2s ease; resize:vertical;}
  .field input:focus,.field textarea:focus{border-color:var(--accent); box-shadow:var(--shadow-focus); background:var(--surface);}
  .field textarea{min-height:130px; line-height:1.6;}
  .field-error{font-size:12px; color:var(--danger-text); font-weight:600;}
  .form-actions{display:flex; gap:12px; margin-top:30px; padding-top:26px; border-top:1px solid var(--border);}
  .btn-publish{background:var(--accent); color:#fff; border:none; padding:13px 26px; border-radius:11px; font-size:14.5px; font-weight:700; transition:.2s ease; box-shadow:0 8px 18px rgba(79,70,229,.22);}
  .btn-publish:hover{background:var(--accent-hover); transform:translateY(-1px);}
  .btn-cancel{background:var(--surface); color:var(--text-muted); border:1px solid var(--border); padding:13px 22px; border-radius:11px; font-size:14.5px; font-weight:600; transition:.2s ease;}
  .btn-cancel:hover{border-color:var(--text-faint); color:var(--text);}
  .global-error{margin-bottom:18px; padding:12px 14px; border-radius:10px; background:var(--danger-bg); color:var(--danger-text); border:1px solid var(--danger-border); font-size:13px; font-weight:600;}
  @media(max-width:1000px){.sidebar{width:70px; padding:20px 10px;} .sidebar .brand span:last-child, .side-label, .side-nav a span, .admin-chip .name, .admin-chip .role, .logout-link{display:none;} .side-nav a{justify-content:center;} .admin-chip{justify-content:center;} .main{padding:26px 20px;}}
  @media(max-width:640px){.form-grid{grid-template-columns:1fr;} .form-card{padding:22px;} .form-actions{flex-direction:column;} .btn-publish,.btn-cancel{width:100%; text-align:center;}}
</style>
</head>
<body>

<div class="admin-shell">
  <aside class="sidebar">
    <a href="dashboard.php" class="brand">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
      <span>LECTORIA</span>
    </a>
    <p class="side-label">Gestion</p>
    <nav class="side-nav">
      <a href="dashboard.php" class="active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>Catalogue</span>
      </a>
      <a href="add_livre.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        <span>Ajouter un livre</span>
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-chip">
        <span class="admin-avatar"></span>
        <div><div class="name">Administrateur</div><div class="role">Back-office</div></div>
      </div>
      <a href="../logout.php" class="logout-link">Se déconnecter</a>
    </div>
  </aside>

  <main class="main">
    <p class="breadcrumb"><a href="dashboard.php">Catalogue</a> / Modifier un livre</p>
    <div class="main-header">
      <div>
        <h1>Modifier un livre</h1>
        <p>Mettez à jour les informations de l'ouvrage sélectionné</p>
      </div>
      <span class="edit-tag">Édition · <?= htmlspecialchars((string) ($donnees['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <?php if (isset($errors['global'])): ?>
      <div class="global-error"><?= htmlspecialchars($errors['global'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php
        $submitLabel = 'Enregistrer les modifications';
        $action = 'edit_livre.php';
        require __DIR__ . '/partials/_livre_form.php';
    ?>
  </main>
</div>

</body>
</html>
