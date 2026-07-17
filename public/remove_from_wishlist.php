<?php
require_once __DIR__ . '/../src/Database/Connexion.php';
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/ListeLecture.php';
require_once __DIR__ . '/../src/Models/Livre.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

AuthService::exigerConnexion();

if (!Csrf::verifierToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit;
}

$idLivre = Validation::validerEntier($_POST['id_livre'] ?? null);
if ($idLivre === null) {
    http_response_code(400);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idLecteur = (int) ($_SESSION['utilisateur_id'] ?? 0);
$liste = ListeLecture::findByLecteur($idLecteur);
$appartient = false;
foreach ($liste as $ligne) {
    if ((int) ($ligne['id_livre'] ?? 0) === $idLivre) {
        $appartient = true;
        break;
    }
}

if (!$appartient) {
    http_response_code(403);
    exit;
}

$pdo = Connexion::getInstance();
$pdo->beginTransaction();

try {
    $retirerOk = ListeLecture::retirer($idLivre, $idLecteur);
    if (!$retirerOk) {
        $pdo->rollBack();
        http_response_code(409);
        exit;
    }

    $incrementOk = Livre::incrementerStock($idLivre);
    if (!$incrementOk) {
        $pdo->rollBack();
        http_response_code(409);
        exit;
    }

    $pdo->commit();

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Livre retiré de votre liste de lecture.',
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    $_SESSION['flash_message'] = 'Livre retiré de votre liste de lecture.';
    header('Location: wishlist.php', true, 302);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erreur lors du retrait de la liste de lecture : ' . $e->getMessage());
    http_response_code(500);
    exit;
}
