<?php

// CONEXIÓN A POSTGRESQL

define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'Universidad');
define('DB_USER',     'postgres');
define('DB_PASSWORD', 'admin');

function getConnection()
{
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        // Mostrar errores de SQL
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
