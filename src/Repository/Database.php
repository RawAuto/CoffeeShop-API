<?php

declare(strict_types=1);

namespace CoffeeShop\Repository;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * 
 * Provides a singleton PDO connection to the MySQL database.
 * Configuration is read from environment variables.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get the database connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Create a new database connection
     */
    private static function createConnection(): PDO
    {
        $host = getenv('DB_HOST') ?: 'mysql';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: 'coffeeshop';
        $username = getenv('DB_USERNAME') ?: 'coffeeshop';
        $password = getenv('DB_PASSWORD') ?: 'secret';

        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException(
                "Database connection failed: " . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }

    /**
     * Execute a simple query (for health checks, etc.)
     */
    public static function query(string $sql): array
    {
        $stmt = self::getInstance()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Reset the connection (useful for testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}

