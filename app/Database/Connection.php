<?php
declare(strict_types=1);

namespace App\Database;

use mysqli;
use RuntimeException;

class Connection
{
    private static ?mysqli $instance = null;

    public static function getInstance(array $config): mysqli
    {
        if (self::$instance instanceof mysqli) {
            return self::$instance;
        }

        $host = $config['host'] ?? 'localhost';
        $user = $config['user'] ?? '';
        $pass = $config['pass'] ?? '';
        $name = $config['name'] ?? '';
        $port = (int) ($config['port'] ?? 3306);

        $mysqli = @new mysqli($host, $user, $pass, $name, $port);
        if ($mysqli->connect_errno) {
            throw new RuntimeException('Database connection failed: ' . $mysqli->connect_error);
        }

        $charset = $config['charset'] ?? 'utf8mb4';
        if (!$mysqli->set_charset($charset)) {
            throw new RuntimeException('Unable to set database charset.');
        }

        self::$instance = $mysqli;

        return self::$instance;
    }
}