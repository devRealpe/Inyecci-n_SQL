<?php

// AUTH: login.php — VULNERABLE (pg_ nativo, sin PDO)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$error = '';
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = getConnection();

    // VULNERABLE: concatenación directa sin sanitizar
    $query = "SELECT * FROM usuarios 
              WHERE username = '$username' 
              AND password = '$password'";

    $_SESSION['last_query'] = $query;

    // pg_query SÍ permite múltiples sentencias separadas por ;
    $result = @pg_query($conn, $query);

    if ($result === false) {
        $error = 'Error SQL: ' . pg_last_error($conn);
    } else {
        $usuario = pg_fetch_assoc($result);

        if ($usuario) {
            $_SESSION['logueado'] = true;
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol']      = $usuario['rol'];
            pg_free_result($result);
            pg_close($conn);
            header('Location: ../../frontend/pages/dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }

    pg_close($conn);
}
