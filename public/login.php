<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

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

$redirect = trim((string) ($_GET['redirect'] ?? ''));
$redirect = str_replace('\\', '/', $redirect);
$redirectPath = parse_url($redirect, PHP_URL_PATH);
$redirectPath = $redirectPath === null ? '' : str_replace('\\', '/', $redirectPath);
$redirectPath = ltrim($redirectPath, '/');
$redirectPath = preg_replace('#^public/#', '', $redirectPath) ?? $redirectPath;

$errorMessage = '';
$emailValue = '';
$flashMessage = '';
if (isset($_SESSION['flash_message']) && is_string($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (AuthService::estConnecte()) {
    if ($redirectPath !== '' && str_starts_with($redirectPath, 'admin/')) {
        if (AuthService::estAdministrateur()) {
            header('Location: ' . $redirectPath, true, 302);
            exit;
        }

        $errorMessage = 'Vous êtes déjà connecté en tant que lecteur. Déconnectez-vous pour réessayer avec un compte administrateur.';
    } else {
        header('Location: index.php', true, 302);
        exit;
    }
}

$csrfToken = Csrf::genererToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit;
    }

    $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
    $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');
    $emailValide = filter_var($emailValue, FILTER_VALIDATE_EMAIL) !== false;

    if (!$emailValide || $motDePasse === '') {
        $errorMessage = 'Identifiants incorrects';
    } else {
        $connexionOk = AuthService::login($emailValue, $motDePasse);

        if ($connexionOk) {
            $redirect = trim((string) ($_GET['redirect'] ?? ''));
            $redirect = str_replace('\\', '/', $redirect);
            $redirectPath = parse_url($redirect, PHP_URL_PATH);
            $redirectPath = $redirectPath === null ? '' : str_replace('\\', '/', $redirectPath);
            $redirectPath = ltrim($redirectPath, '/');
            $redirectPath = preg_replace('#^public/#', '', $redirectPath) ?? $redirectPath;

            $routesConnues = [
                'index.php',
                'results.php',
                'details.php',
                'wishlist.php',
            ];

            $estRouteAdmin = str_starts_with($redirectPath, 'admin/');

            if ($estRouteAdmin) {
                if (($_SESSION['role'] ?? '') === 'administrateur') {
                    $query = parse_url($redirect, PHP_URL_QUERY);
                    $redirectionFinale = $redirectPath;
                    if ($query !== null && $query !== '') {
                        $redirectionFinale .= '?' . $query;
                    }

                    header('Location: ' . $redirectionFinale, true, 302);
                    exit;
                }

                header('Location: index.php', true, 302);
                exit;
            }

            if (in_array($redirectPath, $routesConnues, true)) {
                $query = parse_url($redirect, PHP_URL_QUERY);
                $redirectionFinale = $redirectPath;
                if ($query !== null && $query !== '') {
                    $redirectionFinale .= '?' . $query;
                }

                header('Location: ' . $redirectionFinale, true, 302);
                exit;
            }

            header('Location: index.php', true, 302);
            exit;
        }

        $errorMessage = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — LECTORIA</title>
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
    width:100%; max-width:400px; background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); padding:40px 36px;
  }
  .auth-card h1{font-size:23px; font-weight:800; letter-spacing:-.02em; text-align:center; margin-bottom:6px;}
  .auth-card p.sub{text-align:center; font-size:14px; color:var(--text-muted); margin-bottom:30px;}

  .field{margin-bottom:18px;}
  .field label{display:block; font-size:13.5px; font-weight:600; margin-bottom:7px;}
  .field input{
    width:100%; border:1px solid var(--border); border-radius:10px; padding:12px 14px; font-size:14.5px;
    outline:none; transition:.2s ease; background:var(--bg);
  }
  .field input:focus{border-color:var(--accent); box-shadow:var(--shadow-focus); background:var(--surface);}

  .field-row{display:flex; justify-content:space-between; align-items:center; margin:-6px 0 22px;}
  .forgot{font-size:13px; font-weight:600; color:var(--accent);}
  .forgot:hover{text-decoration:underline;}

  .btn-submit{
    width:100%; background:var(--accent); color:#fff; border:none; padding:14px; border-radius:11px;
    font-size:15px; font-weight:700; transition:.2s ease; box-shadow:0 8px 18px rgba(79,70,229,.22);
  }
  .btn-submit:hover{background:var(--accent-hover); transform:translateY(-1px);}

  .divider{display:flex; align-items:center; gap:12px; margin:26px 0; color:var(--text-faint); font-size:12.5px;}
  .divider::before,.divider::after{content:""; flex:1; height:1px; background:var(--border);}

  .social-actions{display:grid; grid-template-columns:1fr 1fr; gap:12px; margin:18px 0 8px;}
  .social-button{
    display:inline-flex; align-items:center; justify-content:center; gap:10px; width:100%; min-height:44px;
    border:1px solid var(--border); background:var(--surface); border-radius:10px; color:var(--text);
    font-size:13.5px; font-weight:700; padding:10px 14px; transition:.2s ease;
  }
  .social-button:hover{border-color:var(--accent); box-shadow:var(--shadow-focus);}
  .social-button svg{width:16px; height:16px; flex-shrink:0;}

  .back-link,
  .alt-link{display:block; text-align:center; font-size:13.5px; color:var(--text-muted);}
  .back-link:hover,
  .alt-link:hover{color:var(--accent);}

  .form-error,
  .form-success{
    margin:0 0 18px; padding:12px 14px; border-radius:10px; font-size:13px; font-weight:600;
  }
  .form-error{
    background:var(--danger-bg); color:var(--danger-text); border:1px solid var(--danger-border);
  }
  .form-success{
    background:var(--success-bg); color:var(--success-text); border:1px solid rgba(22,163,74,.25);
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
    <h1>Connexion</h1>
    <p class="sub">Créez votre compte lecteur ou connectez-vous à votre espace. L'accès back-office reste réservé à un compte administrateur créé directement en base de données.</p>

    <?php if ($flashMessage !== ''): ?>
      <div class="form-success"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
      <div class="form-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : '' ?>" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
      <div class="field">
        <label for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" placeholder="vous@exemple.com" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="field">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required>
      </div>
      <div class="field-row">
        <span></span>
        <a href="register.php" class="forgot">Créer un compte</a>
      </div>
      <button type="submit" class="btn-submit">Se connecter</button>
    </form>

    <div class="divider">ou</div>

    <div class="social-actions">
      <button type="button" class="social-button" aria-label="Connexion Google">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M21.6 12.23c0-.7-.06-1.38-.17-2.03H12v3.85h5.39a4.6 4.6 0 0 1-2 3.02v2.5h3.23c1.89-1.73 2.98-4.29 2.98-7.34Z" fill="#4285F4"/>
          <path d="M12 22c2.7 0 4.97-.89 6.63-2.42l-3.23-2.5c-.89.6-2.03.96-3.4.96-2.61 0-4.82-1.76-5.6-4.13H.98v2.63A10 10 0 0 0 12 22Z" fill="#34A853"/>
          <path d="M6.4 14.91a6 6 0 0 1 0-3.82V8.46H.98A10 10 0 0 0 0 12c0 1.61.39 3.14 1.08 4.5l5.32-1.59Z" fill="#FBBC05"/>
          <path d="M12 3.98c1.46 0 2.78.5 3.82 1.48l2.86-2.86C16.97.89 14.7 0 12 0A10 10 0 0 0 .98 8.46l5.42 3.63C7.18 5.74 9.39 3.98 12 3.98Z" fill="#EA4335"/>
        </svg>
        Google
      </button>
      <button type="button" class="social-button" aria-label="Connexion Apple">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M16.5 12.2c0-2.02 1.65-2.98 1.72-3.03-0.94-1.38-2.4-1.57-2.92-1.59-1.24-.13-2.42.73-3.05.73-.63 0-1.59-.72-2.62-.7-1.35.02-2.6.79-3.3 2.01-1.41 2.46-.36 6.1 1.02 8.08.68.98 1.48 2.07 2.53 2.03 1.01-.04 1.39-.66 2.61-.66 1.22 0 1.56.66 2.63.63 1.09-.02 1.78-.99 2.45-1.97.77-1.12 1.09-2.21 1.11-2.27-.03-.01-2.14-.82-2.14-3.47Zm-1.96-5.77c.55-.67.93-1.59.82-2.52-.79.03-1.74.52-2.31 1.18-.51.59-.96 1.53-.84 2.43.88.07 1.78-.44 2.33-1.09Z"/>
        </svg>
        Apple
      </button>
    </div>
  </div>
</div>

</body>
</html>
