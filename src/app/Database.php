<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: 'db',
                getenv('DB_PORT') ?: '3306',
                getenv('DB_NAME') ?: 'cart'
            );

            self::$pdo = new PDO($dsn, getenv('DB_USER') ?: 'cart', getenv('DB_PASS') ?: 'cart', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$pdo;
    }
}
