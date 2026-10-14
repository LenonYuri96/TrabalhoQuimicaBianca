<?php
class Database
{
    private static $host = 'localhost';
    private static $db_name = 'lab_control';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false // Garante compatibilidade total com o MariaDB
                    ]
                );
            } catch (PDOException $e) {
                die("Erro de conexão com o banco: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}