<?php
/**
 * Helpers de gestion du jeton CSRF.
 */
class Csrf
{
    /**
     * Génère un jeton CSRF et le stocke en session.
     *
     * @return string
     */
    public static function genererToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie un jeton CSRF fourni par le formulaire.
     *
     * @param ?string $token
     * @return bool
     */
    public static function verifierToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($token === null || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
