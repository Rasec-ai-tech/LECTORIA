<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../../src/Helpers/Validation.php';
require_once __DIR__ . '/../../src/Helpers/Csrf.php';
require_once __DIR__ . '/../../src/Models/ListeLecture.php';
require_once __DIR__ . '/../../src/Models/Livre.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

if (!Csrf::verifierToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit;
}

$idLivre = Validation::validerEntier($_POST['id'] ?? null);
if ($idLivre === null) {
    http_response_code(400);
    exit;
}

$empruntsActifs = ListeLecture::compterEmpruntsActifs($idLivre);
if ($empruntsActifs > 0) {
    $_SESSION['flash_message'] = 'Suppression impossible : ' . $empruntsActifs . ' emprunt(s) en cours empêchent la suppression.';
    header('Location: dashboard.php', true, 302);
    exit;
}

$suppressionOk = Livre::supprimer($idLivre);
if ($suppressionOk) {
    $_SESSION['flash_message'] = 'Le livre a bien été supprimé de la collection.';
    header('Location: dashboard.php', true, 302);
    exit;
}

$_SESSION['flash_message'] = 'Impossible de supprimer ce livre pour le moment.';
header('Location: dashboard.php', true, 302);
exit;
