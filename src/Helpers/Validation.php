<?php
/**
 * Helpers de validation des entrées utilisateur.
 */
class Validation
{
    /**
     * Nettoie une chaîne de caractères en supprimant les espaces et en limitant sa longueur.
     *
     * @param string $valeur
     * @return string
     */
    public static function nettoyerTexte(string $valeur): string
    {
        $valeur = trim($valeur);

        if (strlen($valeur) > 255) {
            $valeur = substr($valeur, 0, 255);
        }

        return $valeur;
    }

    /**
     * Nettoie une chaîne de caractères plus longue en limitant sa longueur.
     *
     * @param string $valeur
     * @param int $longueurMaximale
     * @return string
     */
    public static function nettoyerTexteLong(string $valeur, int $longueurMaximale = 2000): string
    {
        $valeur = trim($valeur);

        if (mb_strlen($valeur, 'UTF-8') > $longueurMaximale) {
            $valeur = mb_substr($valeur, 0, $longueurMaximale, 'UTF-8');
        }

        return $valeur;
    }

    /**
     * Valide une valeur entière et la retourne sous forme d'entier.
     *
     * @param mixed $valeur
     * @return ?int
     */
    public static function validerEntier($valeur): ?int
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        $entier = filter_var($valeur, FILTER_VALIDATE_INT);

        return $entier === false ? null : (int) $entier;
    }
}
