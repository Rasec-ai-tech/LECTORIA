<?php
require_once __DIR__ . '/../Database/Connexion.php';

/**
 * Modèle métier pour les lecteurs.
 */
class Lecteur
{
    /**
     * Recherche un lecteur par son adresse e-mail.
     *
     * @param string $email
     * @return ?array
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, nom, prenom, email, mot_de_passe, role
             FROM Lecteurs
             WHERE email = :email
             LIMIT 1'
        );
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->execute();

        $lecteur = $statement->fetch();

        return $lecteur === false ? null : $lecteur;
    }

    /**
     * Recherche un lecteur par son identifiant.
     *
     * @param int $id
     * @return ?array
     */
    public static function findById(int $id): ?array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, nom, prenom, email, mot_de_passe, role
             FROM Lecteurs
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $lecteur = $statement->fetch();

        return $lecteur === false ? null : $lecteur;
    }

    /**
     * Crée un nouveau lecteur.
     *
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $motDePasseHash
     * @return int
     */
    public static function create(string $nom, string $prenom, string $email, string $motDePasseHash): int
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'INSERT INTO Lecteurs (nom, prenom, email, mot_de_passe, role)
             VALUES (:nom, :prenom, :email, :motDePasseHash, :role)'
        );
        $statement->bindValue(':nom', $nom, PDO::PARAM_STR);
        $statement->bindValue(':prenom', $prenom, PDO::PARAM_STR);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->bindValue(':motDePasseHash', $motDePasseHash, PDO::PARAM_STR);
        $statement->bindValue(':role', 'lecteur', PDO::PARAM_STR);
        $statement->execute();

        return (int) $pdo->lastInsertId();
    }
}
