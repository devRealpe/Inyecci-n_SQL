<?php

// CONEXIÓN A POSTGRESQL — pg_ nativo (sin PDO)

define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'Universidad');
define('DB_USER',     'postgres');
define('DB_PASSWORD', 'admin');

function getConnection()
{
    $connStr = sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASSWORD
    );

    $conn = pg_connect($connStr);

    if (!$conn) {
        die("Error de conexión: " . pg_last_error());
    }

    return $conn;
}
