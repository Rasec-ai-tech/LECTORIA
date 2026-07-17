<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/Lecteur.php';

$appEnv = defined('APP_ENV') ? APP_ENV : 'development';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $appEnv === 'production',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if (AuthService::estConnecte()) {
    header('Location: index.php', true, 302);
    exit;
}

$errorMessage = '';
$nomValue = '';
$prenomValue = '';
$emailValue = '';
$csrfToken = Csrf::genererToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit;
    }

    $nomValue = Validation::nettoyerTexte((string) ($_POST['nom'] ?? ''));
    $prenomValue = Validation::nettoyerTexte((string) ($_POST['prenom'] ?? ''));
    $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
    $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');
    $confirmation = (string) ($_POST['mot_de_passe_confirmation'] ?? '');

    if ($nomValue === '' || $prenomValue === '' || $emailValue === '' || !filter_var($emailValue, FILTER_VALIDATE_EMAIL) || strlen($motDePasse) < 8 || $motDePasse !== $confirmation) {
        $errorMessage = 'Veuillez vérifier les informations saisies.';
    } else {
        $lecteurExistant = Lecteur::findByEmail($emailValue);
        if ($lecteurExistant !== null) {
            $errorMessage = 'Cette adresse e-mail est déjà utilisée.';
        } else {
            $motDePasseHash = password_hash($motDePasse, PASSWORD_ARGON2ID);
            $idLecteur = Lecteur::create($nomValue, $prenomValue, $emailValue, $motDePasseHash);

            if ($idLecteur <= 0) {
                $errorMessage = 'Impossible de créer votre compte pour le moment.';
            } else {
                $_SESSION['flash_message'] = 'Compte créé avec succès. Connectez-vous pour accéder à votre espace.';
                header('Location: login.php', true, 302);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — LECTORIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="assets/js/nav.js" defer></script>
<style>
  :root{
    --bg:#F8FAFC; --surface:#FFFFFF; --text:#0F172A; --text-muted:#64748B; --text-faint:#94A3B8;
    --accent:#4F46E5; --accent-hover:#4338CA; --accent-light:#EEF2FF;
    --border:#E2E8F0;
    --radius:12px; --radius-lg:18px;
    --shadow-md:0 10px 26px rgba(15,23,42,.08);
    --shadow-lg:0 26px 60px rgba(15,23,42,.10);
    --shadow-focus:0 0 0 4px rgba(79,70,229,.14);
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
    font-family:'Inter',system-ui,-apple-system,sans-serif; background:var(--bg); color:var(--text);
    min-height:100vh; display:flex; flex-direction:column;
  }
  a{color:inherit; text-decoration:none;}
  button,input{font-family:inherit;}
  button{cursor:pointer;}

  .top{padding:28px 32px;}
  .brand{display:inline-flex; align-items:center; gap:10px; font-weight:800; font-size:19px;}
  .brand-mark{width:34px; height:34px; border-radius:10px; background:var(--accent); display:flex; align-items:center; justify-content:center;}
  .brand-mark svg{width:18px; height:18px;}

  .auth-wrap{flex:1; display:flex; align-items:center; justify-content:center; padding:20px;}
  .auth-card{
    width:100%; max-width:440px; background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); padding:40px 36px;
  }
  .auth-card h1{font-size:23px; font-weight:800; letter-spacing:-.02em; text-align:center; margin-bottom:6px;}
  .auth-card p.sub{text-align:center; font-size:14px; color:var(--text-muted); margin-bottom:28px;}

  .field{margin-bottom:18px;}
  .field label{display:block; font-size:13.5px; font-weight:600; margin-bottom:7px;}
  .field input{
    width:100%; border:1px solid var(--border); border-radius:10px; padding:12px 14px; font-size:14.5px;
    outline:none; transition:.2s ease; background:var(--bg);
  }
  .field input:focus{border-color:var(--accent); box-shadow:var(--shadow-focus); background:var(--surface);}

  .btn-submit{
    width:100%; background:var(--accent); color:#fff; border:none; padding:14px; border-radius:11px;
    font-size:15px; font-weight:700; transition:.2s ease; box-shadow:0 8px 18px rgba(79,70,229,.22);
  }
  .btn-submit:hover{background:var(--accent-hover); transform:translateY(-1px);}

  .divider{display:flex; align-items:center; gap:12px; margin:26px 0; color:var(--text-faint); font-size:12.5px;}
  .divider::before,.divider::after{content:""; flex:1; height:1px; background:var(--border);}

  .back-link,
  .alt-link{display:block; text-align:center; font-size:13.5px; color:var(--text-muted);}
  .back-link:hover,
  .alt-link:hover{color:var(--accent);}

  .form-error{
    margin:0 0 18px; padding:12px 14px; border-radius:10px; background:var(--danger-bg);
    color:var(--danger-text); border:1px solid var(--danger-border); font-size:13px; font-weight:600;
  }

  @media(max-width:460px){.auth-card{padding:32px 24px;}}
</style>
</head>
<body>

<div class="top">
  <a href="index.php" class="brand">
    <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
    LECTORIA
  </a>
</div>

<div class="auth-wrap">
  <div class="auth-card">
    <h1>Inscription</h1>
    <p class="sub">Créez votre compte lecteur pour rejoindre la bibliothèque. L'accès administrateur est réservé à un compte spécial créé en base de données.</p>

    <?php if ($errorMessage !== ''): ?>
      <div class="form-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="register.php" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
      <div class="field">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nomValue, ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
      </div>
      <div class="field">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenomValue, ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
      </div>
      <div class="field">
        <label for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
      </div>
      <div class="field">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Minimum 8 caractères" required>
      </div>
      <div class="field">
        <label for="mot_de_passe_confirmation">Confirmation du mot de passe</label>
        <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" placeholder="Répétez votre mot de passe" required>
      </div>
      <button type="submit" class="btn-submit">Créer mon compte</button>
    </form>

    <div class="divider">ou</div>
    <a href="login.php" class="alt-link">Déjà inscrit ? Se connecter</a>
  </div>
</div>

</body>
</html>
