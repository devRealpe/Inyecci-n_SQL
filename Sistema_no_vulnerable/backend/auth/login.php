<?php

// AUTH: login.php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$error = '';
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // verificar que no vengan vacíos
    if ($username === '' || $password === '') {
        $error = 'Por favor ingresa usuario y contraseña.';
    } else {
        $pdo = getConnection();

        // PREPARED STATEMENT: los datos del usuario nunca se mezclan con el SQL
        // El "?" es un marcador de posición — la base de datos lo trata como DATO, no como código
        $query = "SELECT * FROM usuarios WHERE username = ? AND password = ?";

        // Guardamos la query en sesión para mostrarla (solo el template, sin los datos)
        $_SESSION['last_query'] = $query . "  -- Parámetros: [protegidos]";

        try {
            $stmt = $pdo->prepare($query);           // Se prepara la consulta
            $stmt->execute([$username, $password]);   // Se pasan los datos por separado
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
            // NUNCA mostramos el error real de SQL al usuario en producción
            // Solo registramos internamente (aquí lo mostramos solo para fines didácticos)
            $error = 'Error de sistema. Intenta de nuevo más tarde.';
            // error_log($e->getMessage()); // En producción real: registrar en log del servidor
        }
    }
}
