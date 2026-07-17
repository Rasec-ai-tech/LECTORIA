<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../../src/Helpers/Csrf.php';
require_once __DIR__ . '/../../src/Models/Livre.php';

$tri = isset($_GET['tri']) ? (string) $_GET['tri'] : 'titre';
$livres = Livre::findAll($tri);
$flashMessage = '';
if (isset($_SESSION['flash_message'])) {
    $flashMessage = (string) $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
$csrfToken = Csrf::genererToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de bord — Admin LECTORIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="../assets/js/admin.js" defer></script>
<style>
  :root{
    --bg:#F8FAFC; --surface:#FFFFFF; --text:#0F172A; --text-muted:#64748B; --text-faint:#94A3B8;
    --accent:#4F46E5; --accent-hover:#4338CA; --accent-light:#EEF2FF;
    --blue-bg:#EFF6FF; --blue-text:#1D4ED8; --blue-hover:#DBEAFE;
    --danger-bg:#FEF2F2; --danger-text:#B91C1C; --danger-hover:#FEE2E2;
    --border:#E2E8F0;
    --radius:12px; --radius-lg:18px;
    --shadow-sm:0 2px 8px rgba(15,23,42,.05);
    --shadow-md:0 10px 24px rgba(15,23,42,.07);
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Inter',system-ui,-apple-system,sans-serif; background:var(--bg); color:var(--text); line-height:1.5;}
  a{color:inherit; text-decoration:none;}
  ul{list-style:none;}
  button{font-family:inherit; cursor:pointer;}

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
  .logout-link:hover{color:var(--danger-text);}

  .main{flex:1; padding:34px 42px; min-width:0;}
  .main-header{display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:28px;}
  .main-header h1{font-size:25px; font-weight:800;}
  .main-header p{font-size:13.5px; color:var(--text-muted); margin-top:5px;}

  .btn-primary{display:inline-flex; align-items:center; gap:8px; background:var(--accent); color:#fff; border:none; padding:11px 20px; border-radius:10px; font-size:14px; font-weight:600; box-shadow:var(--shadow-sm); transition:.2s ease;}
  .btn-primary:hover{background:var(--accent-hover); transform:translateY(-1px); box-shadow:var(--shadow-md);}
  .btn-primary svg{width:16px; height:16px;}

  .kpi-row{display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px;}
  .kpi{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:20px 22px; box-shadow:var(--shadow-sm);}
  .kpi .num{font-size:24px; font-weight:800;}
  .kpi .label{font-size:13px; color:var(--text-muted); margin-top:2px;}

  .flash-message{margin-bottom:18px; padding:12px 16px; border-radius:10px; background:var(--accent-light); color:var(--accent); border:1px solid #C7D2FE; font-size:13px; font-weight:600;}

  .table-card{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); overflow:hidden;}
  table{width:100%; border-collapse:collapse;}
  thead th{text-align:left; font-size:11.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--text-faint); font-weight:700; padding:14px 20px; border-bottom:1px solid var(--border); background:var(--bg);}
  tbody td{padding:14px 20px; font-size:14px; border-bottom:1px solid #F1F5F9; vertical-align:middle;}
  tbody tr:last-child td{border-bottom:none;}
  tbody tr:hover{background:#FAFBFF;}
  .cell-book{display:flex; align-items:center; gap:12px;}
  .mini-cover{width:34px; height:44px; border-radius:6px; background:linear-gradient(135deg,#EEF2FF,#E0E7FF); display:flex; align-items:center; justify-content:center; flex-shrink:0;}
  .mini-cover svg{width:14px; height:14px; color:var(--accent); opacity:.6;}
  .cell-book .title{font-weight:600; font-size:14px;}
  .cell-book .author{font-size:12.5px; color:var(--text-muted);}
  .stock-ok{color:#15803D; font-weight:600;}
  .stock-low{color:#C2410C; font-weight:600;}
  .row-actions{display:flex; gap:8px;}
  .btn-edit,.btn-delete{font-size:12.5px; font-weight:600; padding:7px 13px; border-radius:8px; border:none; transition:.2s ease;}
  .btn-edit{background:var(--blue-bg); color:var(--blue-text);}
  .btn-edit:hover{background:var(--blue-hover);}
  .btn-delete{background:var(--danger-bg); color:var(--danger-text);}
  .btn-delete:hover{background:var(--danger-hover);} 

  @media(max-width:1000px){
    .sidebar{width:70px; padding:20px 10px;}
    .sidebar .brand span:last-child, .side-label, .side-nav a span, .admin-chip .name, .admin-chip .role, .logout-link{display:none;}
    .side-nav a{justify-content:center;}
    .admin-chip{justify-content:center;}
    .kpi-row{grid-template-columns:1fr;}
    .main{padding:26px 20px;}
    table{font-size:13px;}
  }
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
				<div>
					<div class="name">Administrateur</div>
					<div class="role">Back-office</div>
				</div>
			</div>
			<a href="../logout.php" class="logout-link">Se déconnecter</a>
		</div>
	</aside>

	<main class="main">
		<div class="main-header">
			<div>
				<h1>Tableau de bord</h1>
				<p>Gérez la collection de livres de LECTORIA</p>
			</div>
			<a href="add_livre.php" class="btn-primary">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
				Ajouter un nouveau livre
			</a>
		</div>

		<?php if ($flashMessage !== ''): ?>
			<div class="flash-message"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
		<?php endif; ?>

		<div class="kpi-row">
			<div class="kpi"><div class="num"><?= count($livres) ?></div><div class="label">Livres au catalogue</div></div>
			<div class="kpi"><div class="num">0</div><div class="label">Lecteurs inscrits</div></div>
			<div class="kpi"><div class="num">0</div><div class="label">Emprunts en retard</div></div>
		</div>

		<div class="table-card">
			<table>
				<thead>
					<tr>
						<th>Livre</th>
						<th>Éditeur</th>
						<th>Stock</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($livres as $livre): ?>
						<?php
						$id = (int) ($livre['id'] ?? 0);
						$titre = (string) ($livre['titre'] ?? '');
						$auteur = (string) ($livre['auteur'] ?? '');
						$maisonEdition = (string) ($livre['maison_edition'] ?? '');
						$stock = (int) ($livre['nombre_exemplaire'] ?? 0);
						$stockClass = $stock > 0 ? 'stock-ok' : 'stock-low';
						$stockLabel = $stock > 0 ? $stock . ' exemplaire(s)' : 'Rupture de stock';
						?>
						<tr>
							<td>
								<div class="cell-book">
									<span class="mini-cover"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
									<div><div class="title"><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></div><div class="author"><?= htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8') ?></div></div>
								</div>
							</td>
							<td><?= htmlspecialchars($maisonEdition, ENT_QUOTES, 'UTF-8') ?></td>
							<td><span class="<?= htmlspecialchars($stockClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($stockLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
							<td>
								<div class="row-actions">
									<a href="edit_livre.php?id=<?= (int) $id ?>" class="btn-edit">Modifier</a>
									<form method="post" action="delete_livre.php" style="display:inline;">
										<input type="hidden" name="id" value="<?= (int) $id ?>">
										<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
										<button type="button" class="btn-delete" data-book-title="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">Supprimer</button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</main>

</div>

<div id="delete-modal" class="admin-modal hidden" aria-hidden="true">
    <div class="admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 id="delete-modal-title">Confirmer la suppression</h3>
        <p id="delete-modal-message">Voulez-vous vraiment supprimer ce livre ?</p>
        <div class="admin-modal-actions">
            <button type="button" id="cancel-delete" class="btn-cancel">Annuler</button>
            <button type="button" id="confirm-delete" class="btn-delete">Confirmer</button>
        </div>
    </div>
</div>

</body>
</html>
