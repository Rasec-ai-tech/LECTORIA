<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Helpers/Csrf.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/Livre.php';
require_once __DIR__ . '/../src/Models/ListeLecture.php';

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

$pdo = Connexion::getInstance();
$pdo->beginTransaction();

try {
    $livre = Livre::findById($idLivre);
    if ($livre === null || (int) ($livre['nombre_exemplaire'] ?? 0) <= 0) {
        $pdo->rollBack();
        http_response_code(409);
        exit;
    }

    $decrementOk = Livre::decrementerStock($idLivre);
    if (!$decrementOk) {
        $pdo->rollBack();
        http_response_code(409);
        exit;
    }

    $idLecteur = (int) ($_SESSION['utilisateur_id'] ?? 0);
    $ajoutOk = ListeLecture::ajouter($idLivre, $idLecteur);
    if (!$ajoutOk) {
        $pdo->rollBack();
        http_response_code(409);
        exit;
    }

    $pdo->commit();

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Livre ajouté à votre liste de lecture.',
            'nouveauStock' => max(0, (int) ($livre['nombre_exemplaire'] ?? 0) - 1),
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    $_SESSION['flash_message'] = 'Livre ajouté à votre liste de lecture.';
    header('Location: details.php?id=' . $idLivre, true, 302);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erreur lors de l\'ajout à la liste de lecture : ' . $e->getMessage());
    http_response_code(500);
    exit;
}
