<?php

// AUTH: login.php

session_start();
require_once __DIR__ . '/../config/database.php';

$error   = '';
$query   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = getConnection();

    $query = "SELECT * FROM usuarios 
              WHERE username = '$username' 
              AND password = '$password'";

    // Guardamos la query en sesión para mostrarla en el dashboard
    $_SESSION['last_query'] = $query;

    try {
        $stmt   = $pdo->query($query);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $_SESSION['logueado']  = true;
            $_SESSION['username']  = $usuario['username'];
            $_SESSION['rol']       = $usuario['rol'];
            header('Location: ../../frontend/pages/dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } catch (PDOException $e) {
        // Mostramos el error SQL 
        // Esto muestro lo que rompe la inyección
        $error = 'Error SQL: ' . $e->getMessage();
    }
}
