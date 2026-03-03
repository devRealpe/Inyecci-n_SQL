<?php
// VISTA: login.php
session_start();

if (!empty($_SESSION['logueado'])) {
    header('Location: dashboard.php');
    exit;
}

$error        = '';
$queryMostrar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../backend/auth/login.php';
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

        .vuln-badge {
            background: rgba(255, 209, 102, 0.12);
            border: 1px solid #ffd166;
            color: #ffd166;
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            font-size: 0.8rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>

<body>

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
                    <input type="text" id="username" name="username"
                        placeholder="Ej: admin'--"
                        autocomplete="off"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="off">
                </div>
                <button type="submit" class="btn-primary">Iniciar sesión</button>
            </form>

            <hr class="divider">
        </div>
    </div>

    <?php if ($queryMostrar): ?>
        <div class="debug-panel">
            <h3>🔍 Query ejecutada en base de datos</h3>
            <div class="query-box"><?= htmlspecialchars($queryMostrar) ?></div>
        </div>
    <?php endif; ?>

</body>

</html>