<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $config = Config::$database;
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                
                error_log("Attempting DB connection with DSN: $dsn");
                error_log("Username: " . $config['username']);
                
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
                
                error_log("Database connection successful");
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        error_log("Executing query: $sql");
        error_log("Query params: " . json_encode($params));
        
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            
            error_log("Query executed successfully");
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }
}