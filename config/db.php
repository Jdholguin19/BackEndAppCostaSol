<?php
/**
 * db.php  –  conexión única a MySQL (PDO)
 * Inclúyelo con:   require_once __DIR__ . '/config/db.php';
 * Usa getDB() siempre que necesites la conexión.
 */

class DB
{
    // ¡Rellena con tus credenciales!
    private const DB_HOST = 'localhost';           // p.ej. 127.0.0.1
    private const DB_NAME = 'portalao_appCostaSol';             // p.ej. costasol_app
    private const DB_USER = 'portalao_gcoello';        // p.ej. portalao_gcoello
    private const DB_PASS = 'guiCTV321!';       // p.ej. ********
    private const DB_CHARSET = 'utf8mb4';

    /** @var \PDO|null  Instancia única (singleton) */
    private static ?\PDO $pdo = null;

    /** Devuelve una conexión PDO lista para usar */
    public static function getDB(): \PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . self::DB_HOST .
                   ';dbname='    . self::DB_NAME .
                   ';charset='   . self::DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lanza excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch en arrays asociativos
                PDO::ATTR_PERSISTENT         => false,                  // sin conexiones persistentes
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'"
            ];

            self::$pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
        }

        return self::$pdo;
    }

    // Evita instancias directas
    private function __construct() {}
    private function __clone() {}
}
