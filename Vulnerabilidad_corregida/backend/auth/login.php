<?php

// AUTH: login.php - VERSIÓN SEGURA CON PREPARED STATEMENTS

session_start();
require_once __DIR__ . '/../config/database.php';

$error   = '';
$query   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // ══════════════════════════════════════════════════════════════
    //  VALIDACIÓN DE ENTRADA
    // ══════════════════════════════════════════════════════════════

    // 1. Validar que no estén vacíos
    if (empty($username) || empty($password)) {
        $error = 'Usuario y contraseña son obligatorios.';
    }
    // 2. Validar longitud máxima
    elseif (strlen($username) > 50 || strlen($password) > 100) {
        $error = 'Credenciales inválidas.';
    }
    // 3. Validar caracteres permitidos en username (alfanumérico + guión bajo)
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Usuario contiene caracteres no permitidos.';
    } else {
        $pdo = getConnection();

        // ══════════════════════════════════════════════════════════════
        //  PREPARED STATEMENT - PROTECCIÓN CONTRA SQL INJECTION
        // ══════════════════════════════════════════════════════════════

        // ✅ CORRECTO: Usar ? como placeholders
        $query = "SELECT * FROM usuarios 
                  WHERE username = ? 
                  AND password = ?";

        // Guardamos la query en sesión (solo para demostración educativa)
        $_SESSION['last_query'] = $query . " [Params: username='$username', password='***']";

        try {
            // ✅ PASO 1: Preparar la consulta (separa código SQL de datos)
            $stmt = $pdo->prepare($query);

            // ✅ PASO 2: Ejecutar con los parámetros (se escapan automáticamente)
            // NOTA: En producción, $password debería hashearse con password_hash()
            // y compararse con password_verify()
            $stmt->execute([$username, $password]);

            // ✅ PASO 3: Obtener resultado
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $_SESSION['logueado']  = true;
                $_SESSION['username']  = $usuario['username'];
                $_SESSION['rol']       = $usuario['rol'];

                // Registrar login exitoso (auditoría)
                registrarEvento('LOGIN_SUCCESS', $usuario['username']);

                header('Location: ../../frontend/pages/dashboard.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';

                // Registrar intento fallido
                registrarEvento('LOGIN_FAILED', $username);
            }
        } catch (PDOException $e) {
            // ══════════════════════════════════════════════════════════════
            //  MANEJO SEGURO DE ERRORES
            // ══════════════════════════════════════════════════════════════

            // ✅ CORRECTO: Mensaje genérico + log interno
            $error = 'Error en el sistema. Por favor intente más tarde.';

            // Registrar error en log del sistema (no visible al usuario)
            error_log("Error SQL en login: " . $e->getMessage());
            registrarEvento('LOGIN_ERROR', $username, $e->getMessage());
        }
    }
}

/**
 * Función auxiliar para auditoría de eventos de seguridad
 */
function registrarEvento($tipo, $usuario, $detalle = null)
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO auditoria_seguridad (tipo_evento, usuario, detalle, ip_address, user_agent, fecha) 
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $tipo,
            $usuario,
            $detalle,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Error registrando auditoría: " . $e->getMessage());
    }
}
