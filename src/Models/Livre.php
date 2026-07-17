<?php
require_once __DIR__ . '/../Database/Connexion.php';

/**
 * Modèle métier pour les livres.
 */
class Livre
{
    /**
     * Recherche un livre par son identifiant.
     *
     * @param int $id
     * @return ?array
     */
    public static function findById(int $id): ?array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, titre, auteur, description, maison_edition, nombre_exemplaire
             FROM Livres
             WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $livre = $statement->fetch();

        return $livre === false ? null : $livre;
    }

    /**
     * Recherche des livres par terme sur titre et auteur.
     *
     * @param string $terme
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public static function search(string $terme, int $limite, int $offset): array
    {
        $terme = trim($terme);

        if ($terme === '') {
            return [];
        }

        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, titre, auteur, description, maison_edition, nombre_exemplaire
             FROM Livres
             WHERE titre LIKE :termeTitre OR auteur LIKE :termeAuteur
             ORDER BY titre ASC
             LIMIT :limite OFFSET :offset'
        );

        $searchTerm = '%' . $terme . '%';
        $statement->bindValue(':termeTitre', $searchTerm, PDO::PARAM_STR);
        $statement->bindValue(':termeAuteur', $searchTerm, PDO::PARAM_STR);
        $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Compte les résultats de recherche par titre et auteur.
     *
     * @param string $terme
     * @return int
     */
    public static function countSearch(string $terme): int
    {
        $terme = trim($terme);

        if ($terme === '') {
            return 0;
        }

        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT COUNT(id) AS total
             FROM Livres
             WHERE titre LIKE :termeTitre OR auteur LIKE :termeAuteur'
        );

        $searchTerm = '%' . $terme . '%';
        $statement->bindValue(':termeTitre', $searchTerm, PDO::PARAM_STR);
        $statement->bindValue(':termeAuteur', $searchTerm, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetch();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Retourne des suggestions de livres du même auteur.
     *
     * @param string $auteur
     * @param int $excluId
     * @param int $limite
     * @return array
     */
    public static function findByAuteur(string $auteur, int $excluId, int $limite): array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, titre, auteur, description, maison_edition, nombre_exemplaire
             FROM Livres
             WHERE auteur = :auteur AND id != :excluId
             ORDER BY id DESC
             LIMIT :limite'
        );
        $statement->bindValue(':auteur', $auteur, PDO::PARAM_STR);
        $statement->bindValue(':excluId', $excluId, PDO::PARAM_INT);
        $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Retourne tous les livres disponibles paginés.
     *
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public static function findAll(int $limite, int $offset): array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, titre, auteur, description, maison_edition, nombre_exemplaire
             FROM Livres
             ORDER BY titre ASC
             LIMIT :limite OFFSET :offset'
        );
        $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Compte le nombre total de livres disponibles.
     *
     * @return int
     */
    public static function countAll(): int
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->query(
            'SELECT COUNT(id) AS total
             FROM Livres'
        );

        $result = $statement->fetch();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Retourne une liste de livres mis en avant.
     *
     * @param int $limite
     * @return array
     */
    public static function findFeatured(int $limite = 6): array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT id, titre, auteur, description, maison_edition, nombre_exemplaire
             FROM Livres
             ORDER BY id DESC
             LIMIT :limite'
        );
        $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Décrémente le stock d'un livre si un exemplaire est disponible.
     *
     * @param int $id
     * @return bool
     */
    public static function decrementerStock(int $id): bool
    {
        $pdo = Connexion::getInstance();
        $transactionDemarree = !$pdo->inTransaction();

        if ($transactionDemarree) {
            $pdo->beginTransaction();
        }

        try {
            $statement = $pdo->prepare(
                'SELECT nombre_exemplaire
                 FROM Livres
                 WHERE id = :id'
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();

            $livre = $statement->fetch();

            if ($livre === false || (int) $livre['nombre_exemplaire'] <= 0) {
                if ($transactionDemarree) {
                    $pdo->rollBack();
                }
                return false;
            }

            $update = $pdo->prepare(
                'UPDATE Livres
                 SET nombre_exemplaire = nombre_exemplaire - 1
                 WHERE id = :id'
            );
            $update->bindValue(':id', $id, PDO::PARAM_INT);
            $update->execute();

            if ($transactionDemarree) {
                $pdo->commit();
            }

            return true;
        } catch (PDOException $e) {
            if ($transactionDemarree) {
                $pdo->rollBack();
            }
            error_log('Erreur lors de la décrémentation du stock : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Incrémente le stock d'un livre.
     *
     * @param int $id
     * @return bool
     */
    public static function incrementerStock(int $id): bool
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'UPDATE Livres
             SET nombre_exemplaire = nombre_exemplaire + 1
             WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * Crée un livre à partir des données validées.
     *
     * @param array $donnees
     * @return int
     */
    public static function creer(array $donnees): int
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'INSERT INTO Livres (titre, auteur, description, maison_edition, nombre_exemplaire)
             VALUES (:titre, :auteur, :description, :maisonEdition, :nombreExemplaire)'
        );
        $statement->bindValue(':titre', (string) ($donnees['titre'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':auteur', (string) ($donnees['auteur'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':description', (string) ($donnees['description'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':maisonEdition', (string) ($donnees['maison_edition'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':nombreExemplaire', (int) ($donnees['nombre_exemplaire'] ?? 0), PDO::PARAM_INT);
        $statement->execute();

        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour les informations d'un livre.
     *
     * @param int $id
     * @param array $donnees
     * @return bool
     */
    public static function mettreAJour(int $id, array $donnees): bool
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'UPDATE Livres
             SET titre = :titre,
                 auteur = :auteur,
                 description = :description,
                 maison_edition = :maisonEdition,
                 nombre_exemplaire = :nombreExemplaire
             WHERE id = :id'
        );
        $statement->bindValue(':titre', (string) ($donnees['titre'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':auteur', (string) ($donnees['auteur'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':description', (string) ($donnees['description'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':maisonEdition', (string) ($donnees['maison_edition'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':nombreExemplaire', (int) ($donnees['nombre_exemplaire'] ?? 0), PDO::PARAM_INT);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * Supprime un livre.
     *
     * @param int $id
     * @return bool
     */
    public static function supprimer(int $id): bool
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'DELETE FROM Livres
             WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
