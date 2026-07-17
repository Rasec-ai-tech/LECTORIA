<?php
require_once __DIR__ . '/../Database/Connexion.php';

/**
 * Modèle métier pour la liste de lecture.
 */
class ListeLecture
{
    /**
     * Retourne la liste de lecture d'un lecteur avec les informations du livre.
     *
     * @param int $idLecteur
     * @return array
     */
    public static function findByLecteur(int $idLecteur): array
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT ll.id_livre, ll.id_lecteur, ll.date_emprunt, ll.date_retour,
                    l.titre, l.auteur, l.description, l.maison_edition, l.nombre_exemplaire
             FROM Liste_lecture AS ll
             INNER JOIN Livres AS l ON l.id = ll.id_livre
             WHERE ll.id_lecteur = :idLecteur
             ORDER BY ll.date_emprunt DESC'
        );
        $statement->bindValue(':idLecteur', $idLecteur, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Ajoute un livre à la liste de lecture d'un lecteur.
     *
     * @param int $idLivre
     * @param int $idLecteur
     * @return bool
     */
    public static function ajouter(int $idLivre, int $idLecteur): bool
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'INSERT INTO Liste_lecture (id_livre, id_lecteur, date_emprunt, date_retour)
             VALUES (:idLivre, :idLecteur, CURDATE(), NULL)'
        );
        $statement->bindValue(':idLivre', $idLivre, PDO::PARAM_INT);
        $statement->bindValue(':idLecteur', $idLecteur, PDO::PARAM_INT);

        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'ajout à la liste de lecture : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retire un livre de la liste de lecture d'un lecteur après vérification d'appartenance.
     *
     * @param int $idLivre
     * @param int $idLecteur
     * @return bool
     */
    public static function retirer(int $idLivre, int $idLecteur): bool
    {
        $pdo = Connexion::getInstance();

        $check = $pdo->prepare(
            'SELECT id_livre, id_lecteur
             FROM Liste_lecture
             WHERE id_livre = :idLivre AND id_lecteur = :idLecteur
             LIMIT 1'
        );
        $check->bindValue(':idLivre', $idLivre, PDO::PARAM_INT);
        $check->bindValue(':idLecteur', $idLecteur, PDO::PARAM_INT);
        $check->execute();

        if ($check->fetch() === false) {
            return false;
        }

        $statement = $pdo->prepare(
            'DELETE FROM Liste_lecture
             WHERE id_livre = :idLivre AND id_lecteur = :idLecteur'
        );
        $statement->bindValue(':idLivre', $idLivre, PDO::PARAM_INT);
        $statement->bindValue(':idLecteur', $idLecteur, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * Compte le nombre d'emprunts actifs pour un livre.
     *
     * @param int $idLivre
     * @return int
     */
    public static function compterEmpruntsActifs(int $idLivre): int
    {
        $pdo = Connexion::getInstance();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM Liste_lecture
             WHERE id_livre = :idLivre AND date_retour IS NULL'
        );
        $statement->bindValue(':idLivre', $idLivre, PDO::PARAM_INT);
        $statement->execute();

        $resultat = $statement->fetch();

        return (int) ($resultat['total'] ?? 0);
    }
}
