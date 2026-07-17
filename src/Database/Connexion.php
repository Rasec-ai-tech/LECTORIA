<?php
/**
 * Gestion de la connexion PDO unique pour l'application.
 */
class Connexion
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/config.php';

        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            throw new RuntimeException('La configuration de la base de données est incomplète.');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Erreur de connexion PDO : ' . $e->getMessage());
            throw new RuntimeException('Impossible de se connecter à la base de données pour le moment.');
        }
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new RuntimeException('La sérialisation de la connexion n\'est pas autorisée.');
    }

    /**
     * Retourne l'instance PDO unique.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            new self();
        }

        return self::$instance;
    }
}
