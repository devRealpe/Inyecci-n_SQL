<?php
// VISTA: login.php

session_start();

// Si ya está logueado, redirigir al dashboard
if (!empty($_SESSION['logueado'])) {
    header('Location: dashboard.php');
    exit;
}

// Procesar el formulario incluyendo la lógica del backend
$error      = '';
$queryMostrar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../backend/auth/login.php';
    // El archivo login.php redirige si el login es exitoso
    // Si llegamos aquí, hubo error
    $queryMostrar = $_SESSION['last_query'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistema Universidad</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            flex-direction: column;
            gap: 1rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .logo {
            width: 56px;
            height: 56px;
            background: var(--accent-soft);
            border: 1px solid var(--accent);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 1rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .login-header p {
            font-size: 0.88rem;
            color: var(--text-secondary);
            margin-top: 0.3rem;
        }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.4rem 0;
        }

        .hint-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0.9rem 1rem;
            font-size: 0.82rem;
            color: var(--text-secondary);
        }

        .hint-box strong {
            color: var(--warning);
            display: block;
            margin-bottom: 0.4rem;
        }

        .hint-box code {
            background: rgba(255, 255, 255, 0.06);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            color: var(--accent);
            font-size: 0.82rem;
        }

        /* Panel de query visible — fin didáctico */
        .debug-panel {
            width: 100%;
            max-width: 520px;
        }

        .debug-panel h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>

    <!-- ===== FORMULARIO DE LOGIN ===== -->
    <div class="login-wrapper">
        <div class="card">
            <div class="login-header">
                <div class="logo">🎓</div>
                <h1>Universidad</h1>
                <p>Sistema de Gestión Académica</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Ingresa tu usuario"
                        autocomplete="off"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="off">
                </div>

                <button type="submit" class="btn-primary">Iniciar sesión</button>
            </form>

            <hr class="divider">

            <!-- Pista de credenciales — fin didáctico -->
            <div class="hint-box">
                <strong>⚠️ Entorno de pruebas — SQL Injection Demo</strong>
                Credenciales válidas: <code>admin</code> / <code>admin123</code><br><br>
                Intenta inyectar en cualquier campo:<br>
                <code>' OR '1'='1</code> &nbsp;|&nbsp; <code>' OR 1=1--</code>
            </div>
        </div>
    </div>

    <!-- ===== PANEL DIDÁCTICO: muestra la query ejecutada ===== -->
    <?php if ($queryMostrar): ?>
        <div class="debug-panel">
            <h3>🔍 Query ejecutada en base de datos</h3>
            <div class="query-box"><?= htmlspecialchars($queryMostrar) ?></div>
        </div>
    <?php endif; ?>

</body>

</html>