<?php
require_once __DIR__ . '/../src/Helpers/Validation.php';
require_once __DIR__ . '/../src/Models/Livre.php';

header('Content-Type: application/json; charset=UTF-8');

$q = Validation::nettoyerTexte($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([], JSON_THROW_ON_ERROR);
    exit;
}

$livres = Livre::search($q, 8, 0);
$suggestions = [];
foreach ($livres as $livre) {
    $suggestions[] = [
        'id' => (int) ($livre['id'] ?? 0),
        'titre' => (string) ($livre['titre'] ?? ''),
        'auteur' => (string) ($livre['auteur'] ?? ''),
    ];
}

echo json_encode($suggestions, JSON_THROW_ON_ERROR);
