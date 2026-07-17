<?php
require_once __DIR__ . '/../Models/Lecteur.php';

/**
 * Service d'authentification et de gestion des sessions.
 */
class AuthService
{
    /**
     * Connecte un lecteur avec son e-mail et son mot de passe.
     *
     * @param string $email
     * @param string $motDePasse
     * @return bool
     */
    public static function login(string $email, string $motDePasse): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lecteur = Lecteur::findByEmail($email);

        if ($lecteur === null || !password_verify($motDePasse, $lecteur['mot_de_passe'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['utilisateur_id'] = (int) $lecteur['id'];
        $_SESSION['role'] = $lecteur['role'];

        return true;
    }

    /**
     * Déconnecte l'utilisateur courant.
     *
     * @return void
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     *
     * @return bool
     */
    public static function estConnecte(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['utilisateur_id']);
    }

    /**
     * Vérifie si l'utilisateur connecté est un administrateur.
     *
     * @return bool
     */
    public static function estAdministrateur(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur';
    }

    /**
     * Exige une connexion active et redirige vers la page de login si nécessaire.
     *
     * @return void
     */
    public static function exigerConnexion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!self::estConnecte()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/index.php');
            header('Location: /login.php?redirect=' . $redirect, true, 302);
            exit;
        }
    }

    /**
     * Exige des droits d'administration.
     *
     * @return void
     */
    public static function exigerAdministrateur(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!self::estConnecte()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/admin/dashboard.php');
            header('Location: /login.php?redirect=' . $redirect, true, 302);
            exit;
        }

        if (!self::estAdministrateur()) {
            $_SESSION['flash_message'] = 'Accès réservé aux administrateurs.';
            header('Location: /index.php', true, 302);
            exit;
        }
    }
}
